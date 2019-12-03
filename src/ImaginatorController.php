<?php

namespace Bistroagency\Imaginator;

use App\Http\Controllers\Controller;
use Bistroagency\Imaginator\Facades\Imaginator;
use Bistroagency\Imaginator\Services\Uploader;
use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Models\ImaginatorTemplate;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ImaginatorController extends Controller
{
    private $tempDestination;
    private $destination;

    public function __construct()
    {
        $this->uploader = new Uploader;
        $this->tempDestination = public_path(config('imaginator.app.storage.tempDestination'));
        $this->destination = public_path(config('imaginator.app.storage.destination'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        /** @var Model $imaginators */
        $imaginators = Imaginator
            ::query()
            ->with([
                'imaginator_sources',
                'imaginator_template' => static function ($query) {
                    $query->withTrashed();
                },
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $templates = ImaginatorTemplate::orderBy('label', 'asc')->get();

        return view('imaginator::index', [
            'imaginators' => $imaginators,
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $template
     * @param Request $request
     * @return View
     */
    public function create(string $template, Request $request)
    {
        $imaginatorTemplate = ImaginatorTemplate::where('name', $template)->first();

        if (!$imaginatorTemplate) {
            return redirect()->route(config('imaginator.app.routes.as') . 'templates');
        }

        if ($request->filled('imaginator')) {
            $imaginator = Imaginator::getValidated($request->input('imaginator'));

            if (!$imaginator) {
                abort(404, 'Imaginator se nenašel.');
            }

            if ($imaginator->imaginator_template->name !== $template) {
                return redirect()->route(config('imaginator.app.routes.as') . 'create', [
                    'template' => $imaginator->imaginator_template->name,
                    'imaginator' => $imaginator->id,
                ]);
            }
        } else {
            $imaginator = get_imaginator_model();
        }

        $imaginatorSources = $request->filled('imaginator') && $imaginator !== null ? $imaginator->imaginator_sources : [];

        // Transform source and resized attribute

        if (is_a($imaginatorSources, 'Illuminate\Database\Eloquent\Collection')) {
            $imaginatorSources = $imaginatorSources->map(function ($item){
                $item['source'] = $this->uploader->providerDestionation($item['source']);
                if($item['resized']) {

                    $item['resized'] = $this->uploader->providerDestionation($item['resized']);
                }
                return $item;
            })->toArray();
        }

        return view('imaginator::create', [
            'imaginator' => $imaginator ?? get_imaginator_model(),
            'imaginatorTemplate' => $imaginatorTemplate,
            'imaginatorSources' => $imaginatorSources,
            'imaginatorsViewUrl' => route(config('imaginator.app.routes.as') . 'view', $imaginatorTemplate->name),
        ]);
    }

    /**
     * Properly set paths and save created files after pressing the save button.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        ini_set('memory_limit', '-1');
        ini_set('max_input_vars', '2000');
        set_time_limit(0);

        $imaginatorData = $request->input('imaginator');
        $imaginatorData['id'] = $imaginatorData['id'] ?? null;
        $imaginatorSourcesData = $imaginatorData['imaginator_sources'];
        $imaginatorSources = [];

        $imaginator = Imaginator::updateOrCreate(['id' => $imaginatorData['id']], $imaginatorData);

        if ($imaginator) {
            $resized = $this->uploader->generateResizesFromBase($imaginator->id, $imaginatorSourcesData);

            foreach ($imaginatorSourcesData as $imaginatorSourceDataKey => $imaginatorSourceData) {
                $imaginatorSourceData['id'] = $imaginatorSourceData['id'] ?? null;

                $parentFolder = md5($imaginator->id);

                $fileName = pathinfo($imaginatorSourceData['source'], PATHINFO_BASENAME);

                $sourcePath = make_imaginator_path([
                    $parentFolder,
                    $fileName,
                ]);

                $tmpFileDestination = $this->uploader->tmpFilePath($fileName);
                $destinationFilePath = $this->uploader->filePath($sourcePath);

                if ($this->uploader->fileExists($tmpFileDestination))
                {
                    $this->uploader->move($tmpFileDestination, $destinationFilePath);
                }

                $fillData = collect($imaginatorSourceData)
                    ->merge([
                        'imaginator_id' => $imaginator->id,
                        'source' => $destinationFilePath,
                        'resized' => $resized[$imaginatorSourceDataKey]['resized'],
                        'imaginator_variation_id' => $imaginatorSourceData['imaginator_variation_id'],
                    ])
                    ->toArray();

                $imaginatorSource = ImaginatorSource::updateOrCreate([
                    'id' => $imaginatorSourceData['id'],
                    'imaginator_id' => $imaginator->id
                ], $fillData);
                $imaginatorSource->save();

                $imaginatorSources[] = $imaginatorSource;
            }
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Imaginator succesfully saved',
            'imaginator' => $imaginator,
            'imaginatorSources' => $imaginatorSources,
        ]);
    }


    /**
     * Show overview of all Imaginators in one template.
     *
     * @param string $template
     * @return View
     */
    public function view(string $template)
    {
        $imaginatorTemplate = ImaginatorTemplate::where('name', $template)->first();

        if (!$imaginatorTemplate) {
            return redirect()->route(config('imaginator.app.routes.as') . 'templates');
        }

        $imaginators = Imaginator::getValidatedPaginated($imaginatorTemplate);

        if (count($imaginators->items()) < 1) {
            $imaginators = new LengthAwarePaginator([], count([]), 20);
        }

        return view('imaginator::view', [
            'imaginators' => $imaginators,
            'imaginatorTemplate' => $imaginatorTemplate,
            'imaginatorCreateUrl' => route(config('imaginator.app.routes.as') . 'create', $imaginatorTemplate->name),
        ]);
    }

    /**
     * Get one imaginator lazyload object
     *
     * @param $aliasOrId
     * @return JsonResponse
     */
    public function getLazyloadObject($aliasOrId)
    {
        $aliasOrId = is_numeric($aliasOrId) ? (int)$aliasOrId : $aliasOrId;
        $imaginator = Imaginator::getImaginator($aliasOrId);

        if (!$imaginator) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Imaginator not found',
            ], 404);
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Success',
            'lazyloadObject' => $imaginator->getLazyloadObject(),
        ]);
    }

    /**
     * Show templates page.
     *
     * @return View
     */
    public function templates(): View
    {
        $imaginatorTemplates = ImaginatorTemplate::orderBy('label')->get();

        return view('imaginator::templates', [
            'imaginatorTemplates' => $imaginatorTemplates,
        ]);
    }

    /**
     * Uploads a file for Imaginator
     *
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower($file->getClientOriginalExtension());
            $newFileName = time() . '-' . slugify($fileName) . '.' . $extension;
            $variations = $request->filled('variations') ? json_decode($request->input('variations')) : null;

            if ($variations === null) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'You need to choose atleast one variation before uploading'
                ], 400);
            }

            if ($this->uploader->fileExists($newFileName)) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'This file already exists (even though it shouldn\'t)',
                ], 500);
            }

            $this->uploader->uploadAs($file, $newFileName);

            foreach ($variations as $variation) {
                $imaginatorSources[] = [
                    'imaginator_variation_id' => $variation->id,
                    'source' => $this->uploader->tmpFileDestination($newFileName),
                ];
            }

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Files successfully uploaded',
                'imaginatorSources' => $imaginatorSources
            ]);
        }

        return response()->json([
            'status_code' => 400,
            'status_message' => 'No file or file not valid'
        ], 400);
    }

    /**
     * Destroy imaginator
     *
     * @param Imaginator $imaginator
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function destroy(Imaginator $imaginator): RedirectResponse
    {
        /** @var Model $imaginator */
        if ($imaginator->isUsed()) {
            push_error('Imaginátor nejde smazat protože se někde používá');
            return redirect()->back();
        }

        $imaginator->delete();

        push_success('Imaginátor úspešne smazán');
        return redirect()->back();
    }

    /**
     * Destroy all unused Imaginators (use with caution, proper isUsed() function is required in order
     * to fully utilize this function. For further instructions refer to readme.md
     *
     * @return RedirectResponse
     */
    public function destroyAllUnused(): RedirectResponse
    {
        /** @var Model $imaginators */
        $imaginators = Imaginator::get();

        foreach ($imaginators as $imaginator) {
            if (!$imaginator->isUsed()) {
                $imaginator->delete();
            }
        }

        push_success('Nepoužité Imaginátory úspešne smazány');
        return redirect()->back();
    }

    /**
     * Dummy image generation function
     *
     * @deprecated
     */
    public function generateDummyImage($width, $height)
    {
        try {
            $width = (int)$width;
            $height = (int)$height;

            $image = imagecreatetruecolor($width, $height);
            $gray = imagecolorallocate($image, 0xBB, 0xBB, 0xBB);

            imagefilledrectangle($image, 0, 0, $width, $height, $gray);
            imagepng($image);
            imagedestroy($image);

            return response(null, 200, ['Content-type' => 'image/png']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

}

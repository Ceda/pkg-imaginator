<?php

namespace Bistroagency\Imaginator;

use App\Http\Controllers\Controller;
use Bistroagency\Imaginator\Facades\Imaginator;
use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Models\ImaginatorTemplate;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
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
			$imaginator = new Imaginator();
		}

		$imaginatorSources = $request->filled('imaginator') && $imaginator !== null ? $imaginator->imaginator_sources : [];

		return view('imaginator::create', [
			'imaginator' => $imaginator ?? new Imaginator(),
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
		$imaginatorData = $request->input('imaginator');
		$imaginatorData['id'] = $imaginatorData['id'] ?? null;
		$imaginatorSourcesData = $imaginatorData['imaginator_sources'];
		$imaginatorSources = [];

		$imaginator = Imaginator::updateOrCreate(['id' => $imaginatorData['id']], $imaginatorData);

		if ($imaginator) {
			$resized = $this->generateResizesFromBase($imaginator->id, $imaginatorSourcesData);

			foreach ($imaginatorSourcesData as $imaginatorSourceDataKey => $imaginatorSourceData) {
				$imaginatorSourceData['id'] = $imaginatorSourceData['id'] ?? null;
				$parentFolder = md5($imaginator->id);

				$folders = make_imaginator_path([
					$this->destination,
					$parentFolder,
				]);

				if (!File::exists($folders)) {
					File::makeDirectory($folders, 0777, true);
				}

				$filePath = public_path($imaginatorSourceData['source']);
				$fileName = pathinfo($filePath, PATHINFO_BASENAME);

				if (File::exists($filePath)) {
					File::move($filePath, make_imaginator_path([
						$this->destination,
						$parentFolder,
						$fileName,
					]));
				}

				$sourcePath = make_imaginator_path([
					config('imaginator.app.storage.destination'),
					$parentFolder,
					$fileName,
				]);

				$fillData = collect($imaginatorSourceData)
					->merge([
						'imaginator_id' => $imaginator->id,
						'source' => $sourcePath,
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
			$path = time() . '-' . slugify($fileName) . '.' . $extension;
			$variations = $request->filled('variations') ? json_decode($request->input('variations')) : null;

			if ($variations === null) {
				return response()->json([
					'status_code' => 400,
					'status_message' => 'You need to choose atleast one variation before uploading'
				], 400);
			}

			if (file_exists(make_imaginator_path([
				$this->destination,
				$path,
			]))) {
				return response()->json([
					'status_code' => 500,
					'status_message' => 'This file already exists (even though it shouldn\'t)',
				], 500);
			}

			$file->move($this->tempDestination, $path);

			foreach ($variations as $variation) {
				$imaginatorSources[] = [
					'imaginator_variation_id' => $variation->id,
					'source' => make_imaginator_path([
						config('imaginator.app.storage.tempDestination'),
						$path
					]),
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

	/**
	 * Find imaginator or create it if not found
	 *
	 * @param $resources
	 * @param $imaginatorTemplate
	 * @param $anchorPoint
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getOrCreateImaginator($resources, $imaginatorTemplate, $anchorPoint)
	{
		//if an array was supplied instead of a string or int, different rules apply, validate data and act upon it
		if (is_array($resources)) {
			if (!array_key_exists('alias', $resources)) {
				throw new \Exception('Missing alias parameter in supplied array');
			}

			if (!array_key_exists('default', $resources)) {
				throw new \Exception('Missing default source parameter in supplied array');
			}
		}

		$identified = is_string($resources) ? $resources : $resources['alias'];
		$imaginator = Imaginator::getImaginator($identified);

		//if imaginator already exists, return imaginator
		if ($imaginator) {
			return $imaginator;
		}

		//create new imaginator if old doesn't exist
		$newImaginator = new Imaginator();
		$newImaginator->imaginator_template_id = $imaginatorTemplate->id;
		$newImaginator->alias = is_string($resources) ? $resources : null;

		if (is_array($resources)) {
			$newImaginator->alias = $resources['alias'];
		}

		$newImaginator->save();

		//generate sources for imaginator
		(new self)::generateResizesFromPath(
			$resources,
			$newImaginator,
			$imaginatorTemplate->imaginator_variations,
			$anchorPoint);

		//return new imaginator
		return $newImaginator->fresh();
	}

	//TODO old method, it's going to be removed soon, use generateResizesFromPath and crop by coordinates or anchor point

	/**
	 * @param $imaginatorId
	 * @param array $imaginatorSources
	 * @return array
	 * @throws \Exception
	 */
	protected function generateResizesFromBase($imaginatorId, array $imaginatorSources): ?array
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		$imaginator = Imaginator::where('id', $imaginatorId)->first();

		try {
			$generatedResizePaths = [];
			$parentFolder = md5($imaginator->id);

			foreach ($imaginatorSources as $imaginatorSource) {

				$variation = ImaginatorVariation::findOrFail($imaginatorSource['imaginator_variation_id']);

				if (!$variation) {
					continue;
				}

				$variationName = $variation->slug;
				$extension = pathinfo($imaginatorSource['source'], PATHINFO_EXTENSION);
				$baseName = str_replace('.' . $extension, '', pathinfo($imaginatorSource['source'], PATHINFO_BASENAME));

				$quality = $variation->quality;
				$density = $variation->density;

				if (!is_numeric($quality)) {
					return response()->json(['error' => 'Invalid quality value (must be a number)'], 400);
				}

				$validDensities = config('imaginator.app.densities');

				if (!array_key_exists($density, $validDensities)) {
					return response()->json(['error' => 'Image density is not valid'], 400);
				}

				/*$scale = config('imaginator.app.densities')[$density]['scale'];*/
				$suffix = config('imaginator.app.densities')[$density]['suffix'];

				if ($quality < 20) {
					$quality = 20;
				}

				if ($quality > 100) {
					$quality = 100;
				}

				$sourceImage = $imaginatorSource['resized'];

				if (!strpos($sourceImage, 'base64')) {
					$generatedResizePaths[] = [
						'resized' => $imaginatorSource['resized'],
					];
					continue;
				}

				$image = Image::make($sourceImage);
				$image->fit($image->width(), $image->height(), null);

				$folder = make_imaginator_path([
					$this->destination,
					$parentFolder,
					$variationName,
				]);

				if (!File::exists($folder)) {
					File::makeDirectory($folder, 0777, true);
				}

				$newFileName = $baseName . $suffix . '.' . $extension;

				$imaginatorFilePath = make_imaginator_path([
					config('imaginator.app.storage.destination'),
					$parentFolder,
					$variationName,
					$newFileName,
				]);

				$fullImaginatorFilePath = public_path($imaginatorFilePath);

				$image->save($fullImaginatorFilePath, $quality);

				if (
					strtolower(pathinfo($fullImaginatorFilePath, PATHINFO_EXTENSION)) === 'png'
					&& config('imaginator.compression.compress_png')
				) {
					compress_png($fullImaginatorFilePath);
				}

				$generatedResizePaths[] = [
					'resized' => $imaginatorFilePath,
				];
			}

			return $generatedResizePaths;

		} catch (\Exception $e) {
			throw new \Exception($e);
		}
	}

	/**
	 * Generic automatization structure, the next few functions are used to make a clearer and simpler
	 * process while working with Imaginator, removing the need to use the GUI. The following functions
	 * are going to be used in the cropping process of the GUI after frontend remake. The generateResizesFromBase
	 * function will be removed.
	 *
	 * @param $resources
	 * @param $imaginator
	 * @param $imaginatorVariations
	 * @param $anchorPoint
	 * @return mixed
	 * @throws
	 */
	protected static function generateResizesFromPath(
		$resources,
		$imaginator,
		Collection $imaginatorVariations,
		$anchorPoint
	) {
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		try {
			$parentFolder = md5($imaginator->id);

			foreach ($imaginatorVariations as $variation) {
				if (!$variation) {
					return response()->json(['error' => 'Invalid variation'], 400);
				}

				$imagePath = $resources;

				if (is_array($resources)) {
					$imagePath = (array_key_exists($variation->slug, $resources))
						? $resources[$variation->slug]
						: $resources['default'];
				}

				$variationName = $variation->slug;
				$extension = pathinfo($imagePath, PATHINFO_EXTENSION);
				$baseName = str_replace('.' . $extension, '', pathinfo($imagePath, PATHINFO_BASENAME));

				$quality = $variation->quality;
				$density = $variation->density;

				if (!is_numeric($quality)) {
					return response()->json(['error' => 'Invalid quality value (must be a number)'], 400);
				}

				$validDensities = config('imaginator.app.densities');
				$validAnchorPoints = config('imaginator.app.anchor_points');

				if (!array_key_exists($density, $validDensities)) {
					return response()->json(['error' => 'Image density is not valid'], 400);
				}

				if (!array_key_exists($anchorPoint, $validAnchorPoints)) {
					return response()->json(['error' => 'Anchor point is not valid'], 400);
				}

				$suffix = config('imaginator.app.densities')[$density]['suffix'];

				if ($quality < 20) {
					$quality = 20;
				}

				if ($quality > 100) {
					$quality = 100;
				}

				$width = $variation->width;
				$height = $variation->height;

				if ($width < 10) {
					$width = 10;
				}

				if ($width > 4000) {
					$width = 4000;
				}

				if ($height < 10) {
					$height = 10;
				}

				if ($height > 4000) {
					$height = 4000;
				}

				$imageFullPath = public_path($imagePath);

				if (!File::exists($imageFullPath)) {
					return response()->json(['error' => 'File not found'], 400);
				}

				$image = Image::make($imageFullPath);

				$image->fit($width, $height, null, $validAnchorPoints[$anchorPoint]);

				$folder = make_imaginator_path([
					(new self)->destination,
					$parentFolder,
					$variationName,
				]);

				if (!File::exists($folder)) {
					File::makeDirectory($folder, 0777, true);
				}

				$newFileName = $baseName . $suffix . '.' . $extension;
				$originalFileName = $baseName . '.' . $extension;

				$imaginatorFilePath = make_imaginator_path([
					config('imaginator.app.storage.destination'),
					$parentFolder,
					$variationName,
					$newFileName,
				]);

				$imaginatorSourcePath = make_imaginator_path([
					config('imaginator.app.storage.destination'),
					$parentFolder,
					$originalFileName,
				]);

				$originalImageDestinationPath = make_imaginator_path([
					(new self)->destination,
					$parentFolder,
					$originalFileName,
				]);

				$originalFilePath = public_path($imagePath);

				$fullImaginatorFilePath = public_path($imaginatorFilePath);

				//vytvorit subor ale nedegradovat kvalitu
				$image->save($fullImaginatorFilePath, $quality);

				if (
					config('imaginator.compression.compress_png')
					&& strtolower(pathinfo($fullImaginatorFilePath, PATHINFO_EXTENSION)) === 'png'
				) {
					compress_png($fullImaginatorFilePath);
				}

				$fillData = [
					'imaginator_variation_id' => $variation->id,
					'imaginator_id' => $imaginator->id,
					'source' => $imaginatorSourcePath,
					'resized' => $imaginatorFilePath,
				];

				if (File::exists($originalFilePath) && !File::exists($originalImageDestinationPath)) {
					File::copy($originalFilePath, $originalImageDestinationPath);
				}

				$imaginatorSource = new ImaginatorSource($fillData);
				$imaginatorSource->save();
			}

			return response()->json([
				'status_code' => 200,
				'status_message' => 'Success',
			]);
		} catch (\Exception $e) {
			throw new \Exception($e);
		}
	}
}

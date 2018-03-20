<?php

namespace Bistroagency\Imaginator;

use App\Http\Controllers\Controller;
use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Models\ImaginatorTemplate;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ImaginatorLogic extends Controller
{

	private $tempDestination;
	private $destination;

	public function __construct()
	{
		$this->tempDestination = public_path('storage/imaginator/tmp/');
		$this->destination = public_path('storage/imaginator/');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$imaginators = $this->getImaginatorModel()::with([
			'imaginator_sources',
			'imaginator_template' => function ($query) {
				$query->withTrashed();
			},
		])->orderBy('created_at', 'desc')->paginate(30);
		$templates = ImaginatorTemplate::orderBy('label', 'asc')->get();

		return view('imaginator::index', [
			'imaginators' => $imaginators,
			'templates' => $templates,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(string $template, Request $request)
	{
		if (strlen($template)) {
			$imaginatorTemplate = ImaginatorTemplate::where('name', $template)->firstOrFail();
		} else {
			return response()->json([
				'status_code' => 400,
				'status_message' => 'Template not found',
			], 400);
		}

		if ($request->filled('imaginator')) {
			$imaginator = $this->getImaginatorModel()::getValidated($request->input('imaginator'));
			if (!$imaginator) {
				abort(404, 'Imaginator se nenašel.');
			}
			if ($imaginator->imaginator_template->name !== $template) {
				return redirect()->route('imaginator.create', [
					'template' => $imaginator->imaginator_template->name,
					'imaginator' => $imaginator->id,
				]);
			}
		} else {
			$imaginator = $this->getImaginatorModel();
		}

		$imaginatorSources = $request->filled('imaginator') && $imaginator !== null ? $imaginator->imaginator_sources : [];


		return view('imaginator::create', [
			'imaginator' => $imaginator !== null ? $imaginator : $this->getImaginatorModel(),
			'imaginatorTemplate' => $imaginatorTemplate,
			'imaginatorSources' => $imaginatorSources,
			'imaginatorsViewUrl' => route('imaginator.view', $imaginatorTemplate->name),
		]);
	}

	public function store(Request $request)
	{
		$imaginatorData = $request->input('imaginator');
		$imaginatorData['id'] = isset($imaginatorData['id']) ? $imaginatorData['id'] : null;
		$imaginatorSourcesData = $imaginatorData['imaginator_sources'];
		$imaginatorSources = [];

		$imaginator = $this->getImaginatorModel()::updateOrCreate(['id' => $imaginatorData['id']], $imaginatorData);
		if ($imaginator) {
			$resized = $this->generateResizesFromBase($imaginator->id, $imaginatorSourcesData);

			foreach ($imaginatorSourcesData as $imaginatorSourceDataKey => $imaginatorSourceData) {
				$imaginatorSourceData['id'] = isset($imaginatorSourceData['id']) ? $imaginatorSourceData['id'] : null;
				$parentFolder = md5($imaginator->id);

				$folders = base_path('../storage/imaginator/' . $parentFolder);

				if (!File::exists($folders)) {
					File::makeDirectory($folders, 0777, true);
				}

				$filePath = public_path($imaginatorSourceData['source']);
				$fileName = pathinfo($filePath, PATHINFO_BASENAME);

				if (File::exists($filePath)) {
					File::move($filePath, $this->destination . $parentFolder . '/' . $fileName);
				}

				$sourcePath = 'storage/imaginator/' . $parentFolder . '/' . $fileName;

				$fillData = collect($imaginatorSourceData)->merge([
					'imaginator_id' => $imaginator->id,
					'source' => $sourcePath,
					'resized' => $resized[$imaginatorSourceDataKey]['resized'],
					'imaginator_variation_id' => $imaginatorSourceData['imaginator_variation_id'],
				])->toArray();

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
		], 200);
	}

	public function view(string $template)
	{
		if (strlen($template)) {
			$imaginatorTemplate = ImaginatorTemplate::where('name', $template)->firstOrFail();
		} else {
			return response()->json([
				'status_code' => 400,
				'status_message' => 'Template not found',
			], 400);
		}

		$imaginators = $this->getImaginatorModel()::getValidatedPaginated($imaginatorTemplate);

		if (count($imaginators->items()) < 1) {
			$imaginators = [];
		}

		return view('imaginator::view', [
			'imaginators' => $imaginators,
			'imaginatorTemplate' => $imaginatorTemplate,
			'imaginatorCreateUrl' => route('imaginator.create', $imaginatorTemplate->name),
		]);
	}

	/**
	 * uploads a file (for dropzone)
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
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

			if (file_exists($this->tempDestination . $path)) {
				return response()->json([
					'status_code' => 500,
					'status_message' => 'This file already exists (even though it shouldn\'t)',
				], 500);
			}

			$file->move($this->tempDestination, $path);

			foreach ($variations as $variation) {
				$imaginatorSources[] = [
					'imaginator_variation_id' => $variation->id,
					'source' => 'storage/imaginator/tmp/' . $path,
				];
			}

			return response()->json([
				'status_code' => 200,
				'status_message' => 'Files successfully uploaded',
				'imaginatorSources' => $imaginatorSources
			], 200);
		} else {
			return response()->json([
				'status_code' => 400,
				'status_message' => 'No file or file not valid'
			], 400);
		}
	}

	public function destroy($imaginatorId)
	{
		$imaginator = $this->getImaginatorModel()->where('id', $imaginatorId)->firstOrFail();

		if ($imaginator->isUsed()) {
			push_error('Imaginátor nejde smazat protože se někde používá');
			return redirect()->back();
		}

		$imaginator->delete();
		push_success('Imaginátor úspešne smazán');
		return redirect()->back();
	}

	public function destroyAllUnused()
	{
		$imaginators = $this->getImaginatorModel()::get();
		foreach ($imaginators as $imaginator) {
			if (!$imaginator->isUsed()) {
				$imaginator->delete();
			}
		}
		push_success('Nepoužité Imaginátory úspešne smazány');
		return redirect()->back();
	}

	public function dummy($width, $height)
	{
		try {
			$width = intval($width);
			$height = intval($height);

			$image = imagecreatetruecolor($width, $height);
			$gray = imagecolorallocate($image, 0xBB, 0xBB, 0xBB);

			imagefilledrectangle($image, 0, 0, $width, $height, $gray);
			imagepng($image);
			imagedestroy($image);

			$response = response(null, 200, ['Content-type' => 'image/png']);

			return $response;

		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 404);
		}
	}

	protected function getImaginatorModel()
	{
		$model = config('imaginator.app.model');
		return new $model;
	}

	protected function generateResizesFromBase($imaginatorId, array $imaginatorSources)
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		$imaginator = $this->getImaginatorModel()->where('id', $imaginatorId)->first();

		try {
			$generatedResizePaths = [];
			$parentFolder = md5($imaginator->id);
			foreach ($imaginatorSources as $imaginatorSource) {

				$variation = ImaginatorVariation::findOrFail($imaginatorSource['imaginator_variation_id']);

				if (!$variation) {
					return false;
				}

				$variationName = slugify($variation->name);
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

				$folder = public_path('storage/imaginator/' . $parentFolder . '/' . $variationName);

				if (!File::exists($folder)) {
					File::makeDirectory($folder, 0777, true);
				}

				$imaginatorFilePath = 'storage/imaginator/' . $parentFolder . '/' . $variationName . '/' . $baseName . $suffix . '.' . $extension;

				$image->save(public_path($imaginatorFilePath), $quality);

				$generatedResizePaths[] = [
					'resized' => $imaginatorFilePath,
				];
			}

			return $generatedResizePaths;

		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 404);
		}
	}
}

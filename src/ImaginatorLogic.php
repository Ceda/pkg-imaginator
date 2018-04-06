<?php

namespace Bistroagency\Imaginator;

use App\Http\Controllers\Controller;
use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Models\ImaginatorTemplate;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ImaginatorLogic extends Controller
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
			$imaginatorTemplate = ImaginatorTemplate::where('name', $template)->first();
		}

		if (!$imaginatorTemplate) {
			return redirect()->route(config('imaginator.app.routes.as') . 'templates');
		}

		if ($request->filled('imaginator')) {
			$imaginator = $this->getImaginatorModel()::getValidated($request->input('imaginator'));
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
			$imaginator = $this->getImaginatorModel();
		}

		$imaginatorSources = $request->filled('imaginator') && $imaginator !== null ? $imaginator->imaginator_sources : [];


		return view('imaginator::create', [
			'imaginator' => $imaginator !== null ? $imaginator : $this->getImaginatorModel(),
			'imaginatorTemplate' => $imaginatorTemplate,
			'imaginatorSources' => $imaginatorSources,
			'imaginatorsViewUrl' => route(config('imaginator.app.routes.as') . 'view', $imaginatorTemplate->name),
		]);
	}

	/*
	 * Properly set paths and save created files after pressing the save button.
	 */
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

	/*
	 * Show overview of all Imaginators in one template.
	 */
	public function view(string $template)
	{
		if (strlen($template)) {
			$imaginatorTemplate = ImaginatorTemplate::where('name', $template)->first();
		} else {
			return redirect()->route(config('imaginator.app.routes.as') . 'templates');
		}

		$imaginators = $this->getImaginatorModel()::getValidatedPaginated($imaginatorTemplate);

		if (count($imaginators->items()) < 1) {
			$imaginators = new LengthAwarePaginator([], count([]), 20);
		}

		return view('imaginator::view', [
			'imaginators' => $imaginators,
			'imaginatorTemplate' => $imaginatorTemplate,
			'imaginatorCreateUrl' => route(config('imaginator.app.routes.as') . 'create', $imaginatorTemplate->name),
		]);
	}

	/*
	 * Get one imaginator lazyload object
	 */
	public function getLazyloadObject($aliasOrId)
	{
		$aliasOrId = is_numeric($aliasOrId) ? intval($aliasOrId) : $aliasOrId;
		$imaginator = $this->getImaginatorModel()::getImaginator($aliasOrId);

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
		], 200);
	}

	/*
	 * Show templates page.
	 */
	public function templates()
	{
		$imaginatorTemplates = ImaginatorTemplate::orderBy('label')->get();
		return view('imaginator::templates', [
			'imaginatorTemplates' => $imaginatorTemplates,
		]);
	}

	/**
	 * Uploads a file for Imaginator
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
			], 200);
		} else {
			return response()->json([
				'status_code' => 400,
				'status_message' => 'No file or file not valid'
			], 400);
		}
	}

	/*
	 * Destroy Imaginator by ID
	 */
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

	/*
	 * Destroy all unused Imaginators (use with caution, proper isUsed() function is required in order
	 * to fully utilize this function. For further instructions refer to readme.md
	 */
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

	/*
	 * Dummy image generation function
	 */
	public function generateDummyImage($width, $height)
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

	public static function getOrCreateImaginator($aliasOrIdOrPath, $imaginatorTemplate, $anchorPoint)
	{

		$imaginator = (new self)->getImaginatorModel()::getImaginator($aliasOrIdOrPath);

		//if imaginator already exists, return imaginator
		if ($imaginator) {
			return $imaginator;
		}

		//create new imaginator
		$newImaginator = (new self)->getImaginatorModel();
		$newImaginator->imaginator_template_id = $imaginatorTemplate->id;
		$newImaginator->alias = (is_string($aliasOrIdOrPath)) ? $aliasOrIdOrPath : null;
		$newImaginator->save();

		//generate sources for imaginator
		(new self)::generateResizesFromPath($aliasOrIdOrPath, $newImaginator,
			$imaginatorTemplate->imaginator_variations, $anchorPoint);

		//return new imaginator
		return $newImaginator;
	}

	protected function getImaginatorModel()
	{
		$model = config('imaginator.app.model');
		return new $model;
	}

	//TODO old method, it's going to be removed soon, use generateResizesFromPath and crop by coordinates or anchor point
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

	/*
	 * Generic automatization structure, the next few functions are used to make a clearer and simpler
	 * process while working with Imaginator, removing the need to use the GUI. The following functions
	 * are going to be used in the cropping process of the GUI after frontend remake. The generateResizesFromBase
	 * function will be removed.
	 */
	protected static function generateResizesFromPath(
		$imagePath,
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

				$variationName = slugify($variation->name);
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

				//vytvorit subor ale nedegradovat kvalitu
				$image->save(public_path($imaginatorFilePath), $quality);

				$fillData = [
					'imaginator_variation_id' => $variation->id,
					'imaginator_id' => $imaginator->id,
					'source' => $imaginatorSourcePath,
					'resized' => $imaginatorFilePath,
				];

				$imaginatorSource = new ImaginatorSource($fillData);
				$imaginatorSource->save();
			}

			$filePath = public_path($imagePath);
			$fileName = pathinfo($filePath, PATHINFO_BASENAME);

			if (File::exists($filePath)) {
				File::copy($filePath, make_imaginator_path([
					(new self)->destination,
					$parentFolder,
					$fileName,
				]));
			}

			return response()->json([
				'status_code' => 200,
				'status_message' => 'Success',
			], 200);

		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 404);
		}
	}
}

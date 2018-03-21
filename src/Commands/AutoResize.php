<?php

namespace Bistroagency\Imaginator\Commands;

use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class AutoResize extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'imaginator:auto-resize';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Automatically resize images for new variations or with lost resizes.';

	protected $tempDestination;
	protected $destination;

	public $startTime;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->tempDestination = config('imaginator.app.storage.tempDestination');
		$this->destination = config('imaginator.app.storage.destination');
		$this->startTime = microtime(true);
	}

	public function getElapsedTime()
	{
		return round(microtime(true) - $this->startTime, 3);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		return true;
	}

	//TODO function will be used for the auto-resize
	public static function rebuildResizes($imaginatorId, array $imaginatorSources, $anchorPoint = 'c')
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		$imaginator = (new self)->getImaginatorModel()->where('id', $imaginatorId)->firstOrFail();

		try {
			$parentFolder = md5($imaginator->id);
			foreach ($imaginatorSources as $imaginatorSource) {
				$variation = ImaginatorVariation::findOrFail($imaginatorSource->imaginator_variation_id);

				if (!$variation) {
					return response()->json(['error' => 'Invalid variation'], 400);
				}
				$variationName = slugify($variation->name);
				$extension = pathinfo($imaginatorSource->source, PATHINFO_EXTENSION);
				$baseName = str_replace('.' . $extension, '', pathinfo($imaginatorSource->source, PATHINFO_BASENAME));

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

				$sourceImage = $imaginatorSource->source;

				$imagePath = File::exists(public_path('assets/uploads-versioned/' . $sourceImage)) ?
					public_path('assets/uploads-versioned/' . $sourceImage) :
					public_path('storage/uploads/' . $sourceImage);

				if (!File::exists($imagePath)) {
					return response()->json(['error' => 'File not found'], 400);
				}

				$image = Image::make($imagePath);

				$image->fit($width, $height, null, $validAnchorPoints[$anchorPoint]);

				$folder = public_path('storage/imaginator/' . $parentFolder . '/' . $variationName);

				if (!File::exists($folder)) {
					File::makeDirectory($folder, 0777, true);
				}

				$imaginatorFilePath = 'storage/imaginator/' . $parentFolder . '/' . $variationName . '/' . $baseName . $suffix . '.' . $extension;
				$imaginatorSourcePath = 'storage/imaginator/' . $parentFolder . '/' . $baseName . '.' . $extension;

				//vytvorit subor ale nedegradovat kvalitu
				$image->save(public_path($imaginatorFilePath), $quality);

				$copySource = File::copy($imagePath, public_path($imaginatorSourcePath));

				$updateData = [
					'source' => $imaginatorSourcePath,
					'resized' => $imaginatorFilePath,
				];

				$imaginatorSource->update($updateData);
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

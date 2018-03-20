<?php

namespace Bistroagency\Imaginator\Commands;

use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Models\ImaginatorTemplate;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Refresh extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'imaginator:refresh';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add, modify and delete all Imaginators defined by a config.';

	/**
	 * Imaginator schema collection
	 *
	 * @var array
	 */
	protected $schemas;

	/**
	 * Start time
	 *
	 * @var string
	 */
	public $startTime;

	private $tempDestination;
	private $destination;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->startTime = microtime(true);
		$this->schemas = collect(config('imaginator.schemas'));
		$this->tempDestination = public_path('storage/imaginator/tmp/');
		$this->destination = public_path('storage/imaginator/');
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
		$activeTemplates = [];
		$databaseTemplates = [];
		for ($templateIndex = 0; $templateIndex < count($this->schemas); $templateIndex++) {
			$currentTemplate = collect($this->schemas[$templateIndex])->merge(['deleted_at' => null]);

			$imageTemplate = ImaginatorTemplate::withTrashed()->updateOrCreate([
				'name' => $currentTemplate['name']
			], $currentTemplate->except('variations')->toArray());
			$activeTemplates['templates'][] = $currentTemplate['name'];

			if ($imageTemplate) {
				for ($variationIndex = 0; $variationIndex < count($this->schemas[$templateIndex]['variations']); $variationIndex++) {
					$currentVariation = collect($this->schemas[$templateIndex]['variations'][$variationIndex])->merge([
						'imaginator_template_id' => $imageTemplate->id,
						'deleted_at' => null
					]);

					if (isset($currentVariation['hasTranslation']) && $currentVariation['hasTranslation']) {
						//generate translated variation is hasTranslation in schema
						$varName = $currentVariation['name'];
						$regularWidth = $currentVariation['width'];
						$regularHeight = $currentVariation['height'];
						$regularQuality = $currentVariation['quality'];
						foreach (config('imaginator.app.locales') as $locale) {
							$newVarName = $locale === 'cs' ? $varName . ' (cs)' : $varName . ' (en)';
							$currentVariation = $currentVariation->merge([
								'locale' => $locale,
								'name' => $newVarName,
								'density' => 'regular',
								'width' => $regularWidth,
								'height' => $regularHeight,
								'quality' => $regularQuality,
							]);

							$translatedVariation = ImaginatorVariation::withTrashed()->updateOrCreate([
								'name' => $currentVariation['name'],
								'imaginator_template_id' => $currentVariation['imaginator_template_id'],
							], $currentVariation->toArray());

							$activeTemplates['variations'][] = $imageTemplate->id . '|' . $translatedVariation->name;

							if ($currentVariation['hasRetina'] === true) {
								$activeTemplates = $this->generateRetina($currentVariation, $imageTemplate, $activeTemplates);
							}
						}
					} else {
						//generate regular variation if is not translated (locale = all)
						$imageVariation = ImaginatorVariation::withTrashed()->updateOrCreate([
							'name' => $currentVariation['name'],
							'imaginator_template_id' => $currentVariation['imaginator_template_id']
						], $currentVariation->toArray());

						$activeTemplates['variations'][] = $imageTemplate->id . '|' . $imageVariation->name;

						if ($currentVariation['hasRetina'] === true) {
							$activeTemplates = $this->generateRetina($currentVariation, $imageTemplate, $activeTemplates);
						}
					}
				}
			}

			$this->info('Refreshing template ' . $imageTemplate->name . ', done in ' . $this->getElapsedTime() . 's');
			$this->startTime = microtime(true);
		}

		$templatesFromDatabase = ImaginatorTemplate::all();
		$variantsFromDatabase = ImaginatorVariation::all();
		foreach ($templatesFromDatabase as $templateFromDatabase) {
			$databaseTemplates['templates'][] = $templateFromDatabase->name;
		}

		foreach ($variantsFromDatabase as $variationFromDatabase) {
			$databaseTemplates['variations'][] = $variationFromDatabase->imaginator_template_id . '|' . $variationFromDatabase->name;
		}

		$templatesDifference = array_diff($databaseTemplates['templates'], $activeTemplates['templates']);
		$variationsDifference = array_diff($databaseTemplates['variations'], $activeTemplates['variations']);

		foreach ($variationsDifference as $removedVariationData) {
			$removedVariation = explode('|', $removedVariationData);
			$removedVariationImageTemplateId = $removedVariation[0];
			$removedVariationName = $removedVariation[1];

			$removedVariationsQuery = ImaginatorVariation
				::where('imaginator_template_id', $removedVariationImageTemplateId)
				->where('name', $removedVariationName);

			foreach ($removedVariationsQuery->get() as $removedVariation) {
				$removedSourcesQuery = ImaginatorSource
					::where('imaginator_variation_id', $removedVariation->id);

				foreach ($removedSourcesQuery->get() as $removedSource) {

					if (File::exists($removedSource->resized)) {
						File::delete($removedSource->resized);
					}

					$directoryName = slugify($removedSource->imaginator_variation->name);
					$parentFolderName = md5($removedSource->imaginator->id) . '/';

					$directory = $this->destination . $parentFolderName . $directoryName;

					if (File::exists($directory)) {
						File::deleteDirectory($directory);
					}
				}
				$removedSourcesQuery->delete();
			}

			$removedVariationsQuery->delete();

		}

		foreach ($templatesDifference as $removedTemplateName) {
			$removedTemplate = ImaginatorTemplate::where('name', $removedTemplateName)->first();
			$removedTemplate->imaginator_variations()->delete();
			$removedTemplate->delete();
		}

		return true;
	}

	protected function generateRetina($currentVariation, $imageTemplate, $activeTemplates)
	{
		$currentVariation['name'] = $currentVariation['name'] . ' - retina';
		$currentVariation['density'] = 'retina';
		$currentVariation['width'] = ($currentVariation['width'] * 2);
		$currentVariation['height'] = ($currentVariation['height'] * 2);
		$currentVariation['quality'] = 30;

		$retinaVariation = ImaginatorVariation::withTrashed()->updateOrCreate([
			'name' => $currentVariation['name'],
			'imaginator_template_id' => $currentVariation['imaginator_template_id'],
			'density' => 'retina',
		], $currentVariation->toArray());

		$activeTemplates['variations'][] = $imageTemplate->id . '|' . $retinaVariation->name;

		return $activeTemplates;
	}
}

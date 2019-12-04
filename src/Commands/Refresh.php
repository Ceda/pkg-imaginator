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
    $this->tempDestination = public_path(config('imaginator.app.storage.tempDestination'));
    $this->destination = public_path(config('imaginator.app.storage.destination'));
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
    //init activeTemplates array
    $activeTemplates = [];

    for ($templateIndex = 0; $templateIndex < count($this->schemas); $templateIndex++) {
      $currentTemplate = collect($this->schemas[$templateIndex])->merge(['deleted_at' => null]);

      $imageTemplate = ImaginatorTemplate::withTrashed()->updateOrCreate([
        'name' => $currentTemplate['name']
      ], $currentTemplate->except('variations')->toArray());
      $activeTemplates['templates'][] = $currentTemplate['name'];

      if ($imageTemplate) {
        for ($variationIndex = 0; $variationIndex < count($this->schemas[$templateIndex]['variations']); $variationIndex++) {
          $variationSchemaFromConfig = $this->schemas[$templateIndex]['variations'][$variationIndex];
          $currentVariation = collect($variationSchemaFromConfig)->merge([
            'imaginator_template_id' => $imageTemplate->id,
            'slug' => slugify($variationSchemaFromConfig['name']),
            'deleted_at' => null
          ]);

          if ($currentVariation->has('hasTranslation') && $currentVariation['hasTranslation']) {
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
                'slug' => slugify($newVarName),
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
                $activeTemplates = $this->generateRetina($currentVariation, $imageTemplate,
                  $activeTemplates);
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
              $activeTemplates = $this->generateRetina($currentVariation, $imageTemplate,
                $activeTemplates);
            }
          }
        }
      }

      $this->info('Refreshing template ' . $imageTemplate->name . ', done in ' . $this->getElapsedTime() . 's');
      $this->startTime = microtime(true);
    }

    $this->cleanTemplatesAndVariations($activeTemplates);

    $variations = ImaginatorVariation::get();

    //if empty slugs exist, call fixVariationSlugs function
    if ($variations->contains('slug', null)) {
      $this->fixVariationSlugs($variations);
    }

    return true;
  }

  //function called when hasRetina = true in schemas
  private function generateRetina($currentVariation, $imageTemplate, $activeTemplates)
  {
    $currentVariation['name'] = $currentVariation['name'] . ' - retina';
    $currentVariation['slug'] = slugify($currentVariation['name']);
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

  private function fixVariationSlugs($variations)
  {
    //reset timer
    $this->startTime = microtime(true);

    foreach ($variations as $variation) {
      //update slug
      $variation->slug = slugify($variation->name);
      $variation->save();
    }

    return $this->info('Variation slugs fixed, done in ' . $this->getElapsedTime() . 's');
  }

  private function cleanTemplatesAndVariations(array $activeTemplates)
  {
    //reset timer
    $this->startTime = microtime(true);
    //init empty array
    $databaseTemplates = [];

    //get all templates and variations
    $templatesFromDatabase = ImaginatorTemplate::all();
    $variationsFromDatabase = ImaginatorVariation::all();

    //put template names into an array
    foreach ($templatesFromDatabase as $templateFromDatabase) {
      $databaseTemplates['templates'][] = $templateFromDatabase->name;
    }

    //put variation template ids and names into an array
    foreach ($variationsFromDatabase as $variationFromDatabase) {
      $databaseTemplates['variations'][] = $variationFromDatabase->imaginator_template_id . '|' . $variationFromDatabase->name;
    }

    //get all removed templates
    $templatesDifference = array_diff($databaseTemplates['templates'], $activeTemplates['templates']);
    //get all removed variations
    $variationsDifference = array_diff($databaseTemplates['variations'], $activeTemplates['variations']);

    foreach ($variationsDifference as $removedVariationData) {
      //get removed variation data
      $removedVariation = explode('|', $removedVariationData);
      //get removed variation image template id
      $removedVariationImageTemplateId = $removedVariation[0];
      //get removed variation name
      $removedVariationName = $removedVariation[1];

      //generate removed variations query, used in later functions
      $removedVariationsQuery = ImaginatorVariation
        ::where('imaginator_template_id', $removedVariationImageTemplateId)
        ->where('name', $removedVariationName);

      foreach ($removedVariationsQuery->get() as $removedVariation) {
        //get all removed sources
        $removedSourcesQuery = ImaginatorSource
          ::where('imaginator_variation_id', $removedVariation->id);

        foreach ($removedSourcesQuery->get() as $removedSource) {
          //delete imaginator source file
          if (File::exists($removedSource->resized)) {
            File::delete($removedSource->resized);
          }

          $directoryName = slugify($removedSource->imaginator_variation->name);
          $parentFolderName = md5($removedSource->imaginator->id) . '/';

          $directory = $this->destination . $parentFolderName . $directoryName;

          //delete imaginator source directory
          if (File::exists($directory)) {
            File::deleteDirectory($directory);
          }
        }
        //remove imaginator source from database
        $removedSourcesQuery->delete();
      }
      //remove imaginator variation from database
      $removedVariationsQuery->delete();
    }

    foreach ($templatesDifference as $removedTemplateName) {
      $removedTemplate = ImaginatorTemplate::where('name', $removedTemplateName)->first();
      $removedTemplate->imaginator_variations()->delete();
      $removedTemplate->delete();
    }

    return $this->info('Imaginator templates and variations cleaned up, done in ' . $this->getElapsedTime() . 's');
  }
}

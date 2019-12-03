<?php

namespace Bistroagency\Imaginator\Services;

use Illuminate\Support\Facades\Storage;
use Bistroagency\Imaginator\Models\ImaginatorSource;
use Bistroagency\Imaginator\Facades\Imaginator;
use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class Uploader
{
    private $destinationPrefix;
    private $provider;

    public function __construct()
    {
        if (!config('imaginator.app.storage_provider')) {
            throw new \ErrorException('Storage provider not configured"');
        }

        $this->provider = config('imaginator.app.storage_provider');
        $this->tempDestinationPrefix = config('imaginator.app.storage.tempDestination');
        $this->destination = config('imaginator.app.storage.destination');
    }

    public function fileExists($file)
    {
        return Storage::disk($this->provider)->exists($file);
    }

    public function move($from, $to)
    {
        return Storage::disk($this->provider)->move($from, $to);
    }

    public function providerDestionation($file)
    {
        return Storage::disk($this->provider)->url($file);
    }

    public function tmpFileDestination($fileName)
    {
        return Storage::disk($this->provider)->url($this->tempDestinationPrefix .'/'. $fileName);
    }

    public function fileDestination($fileName)
    {
        return Storage::disk($this->provider)->url($this->destination .'/'. $fileName);
    }

    public function tmpFilePath($fileName)
    {
        return $this->tempDestinationPrefix .'/'. $fileName;
    }

    public function filePath($fileName)
    {
        return $this->destination .'/'. $fileName;
    }

    public function uploadAs($file, $filename)
    {
        return Storage::disk($this->provider)->putFileAs($this->tempDestinationPrefix, $file, $filename);
    }


    /**
     * @param $imaginatorId
     * @param array $imaginatorSources
     * @return array
     * @throws \Exception
     */
    public function generateResizesFromBase($imaginatorId, array $imaginatorSources): ?array
    {
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

                $newFileName = $baseName . $suffix . '.' . $extension;

                $imaginatorFilePath = make_imaginator_path([
                    config('imaginator.app.storage.destination'),
                    $parentFolder,
                    $variationName,
                    $newFileName,
                ]);


                Storage::disk($this->provider)->put($imaginatorFilePath, (string) $image->encode($extension, $quality));

                // TODO Optimalize PNGS
                // if (
                //     strtolower(pathinfo($fullImaginatorFilePath, PATHINFO_EXTENSION)) === 'png'
                //     && config('imaginator.compression.compress_png')
                // ) {
                //     compress_png($fullImaginatorFilePath);
                // }

                $generatedResizePaths[] = [
                    'resized' => $imaginatorFilePath,
                ];
            }

            return $generatedResizePaths;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}

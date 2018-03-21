<?php

namespace Bistroagency\Imaginator\Commands;

use Bistroagency\Imaginator\Models\ImaginatorVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanFiles extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'imaginator:clean-files';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clean Imaginator temporary and junk files.';

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
		//if imaginator directories don't exists, create them!
		if(!File::exists($this->tempDestination)) File::makeDirectory($this->tempDestination, 0777, true);
		if(!File::exists($this->destination)) File::makeDirectory($this->destination, 0777, true);

		$variations = ImaginatorVariation::get();
		$imaginatorDirectories = File::directories($this->destination);
		//clean old variations from folders
		foreach($imaginatorDirectories as $imaginatorDirectory) {
			$variatonDirectories = File::directories($imaginatorDirectory);
			foreach($variatonDirectories as $variationDirectory) {
				$dir = $variationDirectory;
				$dirname = File::name($variationDirectory);
				$variationNames = [];
				foreach($variations as $variation) {
					$variationNames[] = slugify($variation->name);
				}
				if(!in_array($dirname, $variationNames)){
					File::deleteDirectory($dir);
				}

			}
		}

		//delete temporary files but preserve parent folder
		File::deleteDirectory($this->tempDestination, true);
		$this->info('Successfully deleted temporary and junk files done in '.$this->getElapsedTime().'s');
		return true;
	}
}

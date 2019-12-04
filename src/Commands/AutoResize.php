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
}

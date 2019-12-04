<?php

namespace Bistroagency\Imaginator\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * Class Imaginator
 * @package Bistroagency\Imaginator\Facades
 * @return Model
 */
class Imaginator extends Facade
{
  protected static function getFacadeAccessor(): string
  {
    return 'imaginator';
  }
}
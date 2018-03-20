<?php

namespace Bistroagency\Imaginator\Facades;

use Illuminate\Support\Facades\Facade;

class Imaginator extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'bistroagency-imaginator';
	}
}
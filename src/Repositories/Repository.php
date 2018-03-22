<?php

namespace Bistroagency\Imaginator\Repositories;

abstract class Repository
{
	protected $collection = false;

	public function get()
	{
		if ($this->collection === false) {
			$this->collection = $this->collect();
		}

		return $this->collection;
	}

	protected function collect()
	{
		return collect();
	}
}

<?php

namespace Bistroagency\Imaginator\Repositories;

use Bistroagency\Imaginator\Models\Imaginator;

class ImaginatorRepository extends Repository
{
	protected function collect()
	{
		return Imaginator::with([
			'imaginator_sources',
			'imaginator_template'
		])
			->get();
	}
}

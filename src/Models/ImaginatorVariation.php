<?php

namespace Bistroagency\Imaginator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImaginatorVariation extends Model
{
	use SoftDeletes;

	protected $casts = [
		'width' => 'integer',
		'height' => 'integer',
		'quality' => 'integer',
	];

	protected $fillable = [
		'imaginator_template_id',
		'name',
		'breakpoint',
		'locale',
		'density',
		'quality',
		'width',
		'height',
		'anchor_point',
		'deleted_at',
	];

	public $appends = ['source'];

	protected $with = ['imaginator_sources'];

	public function imaginator_template()
	{
		return $this->belongsTo(ImaginatorTemplate::class);
	}

	public function imaginator_sources()
	{
		return $this->hasMany(ImaginatorSource::class);
	}

	public function getSourceAttribute()
	{
		return '';
	}
}
<?php

namespace Bistroagency\Imaginator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImaginatorTemplate extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'name',
    'label',
    'description',
    'deleted_at',
  ];

  public function imaginator_variations()
  {
    return $this->hasMany(ImaginatorVariation::class);
  }

  public function imaginators()
  {
    return $this->hasMany(Imaginator::class);
  }
}

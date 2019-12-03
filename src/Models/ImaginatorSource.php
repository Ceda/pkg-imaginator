<?php

namespace Bistroagency\Imaginator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImaginatorSource extends Model
{
    use SoftDeletes;

    protected $casts = [
        'imaginator_variation_id' => 'integer',
    ];

    protected $fillable = [
        'imaginator_variation_id',
        'imaginator_id',
        'source',
        'resized',
    ];

    public function imaginator_variation()
    {
        return $this->belongsTo(ImaginatorVariation::class);
    }

    public function imaginator()
    {
        return $this->belongsTo(Imaginator::class);
    }
}

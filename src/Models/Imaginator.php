<?php

namespace Bistroagency\Imaginator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class Imaginator extends Model
{
	use SoftDeletes;

	protected $attributes = [
		'imaginator_template_id' => null,
		'alias' => null,
	];

	protected $fillable = [
		'imaginator_template_id',
		'alias',
	];

	public function imaginator_template()
	{
		return $this->belongsTo(ImaginatorTemplate::class);
	}

	public function imaginator_sources()
	{
		return $this->hasMany(ImaginatorSource::class);
	}

	public function getLazyloadObject($locale = null)
	{
		if (!$this) {
			return json_encode([]);
		}

		$lazyArray = [];
		$variations = $this->imaginator_template->imaginator_variations;

		for ($i = 0; $i < $variations->count(); $i++) {
			if ($variations[$i]->locale === 'all' || $variations[$i]->locale === $locale) {
				$breakpoint = config('imaginator.app.breakpoints')[$variations[$i]->breakpoint];
				$density = $variations[$i]->density;
				$lazyArray[$breakpoint][$density] = isset($this->imaginator_sources[$i]) ? $this->imaginator_sources[$i]->resized : '';
			}
		}

		return json_encode($lazyArray);
	}

	public function isUsed()
	{
		return false;
	}

	public function getPreviewImageUrl()
	{
		return ($this->imaginator_sources->first() && strlen($this->imaginator_sources->first()->source)) ?
			url($this->imaginator_sources->first()->source) :
			false;
	}

	public static function getValidatedPaginated(ImaginatorTemplate $imaginatorTemplate = null, $pagination = 20)
	{
		$imaginatorsQuery = self
			::with([
				'imaginator_template',
				'imaginator_sources',
			]);

		if($imaginatorTemplate) {
			$imaginatorsQuery = $imaginatorsQuery->where('imaginator_template_id', $imaginatorTemplate->id);
		}

		foreach ($imaginatorsQuery->get() as $imaginator) {
			foreach ($imaginator->imaginator_sources as $imaginatorSource) {
				$sourcePath = str_replace('//', '/', public_path($imaginatorSource->source));
				$resizedPath = str_replace('//', '/', public_path($imaginatorSource->resized));

				if (!File::exists($resizedPath)) {
					$imaginatorSource->resized = null;
					$imaginatorSource->save();
				}

				if (File::exists($sourcePath) && !File::isDirectory($sourcePath)) {
					continue;
				}

				$imaginatorSource->delete();
			}
		}

		return $imaginatorsQuery->paginate($pagination);
	}

	public static function getValidated($imaginator_id)
	{
		$imaginatorQuery = self
			::with([
				'imaginator_sources',
			])
			->where('id', $imaginator_id);

		if(!$imaginatorQuery->first()) {
			return false;
		}

		foreach ($imaginatorQuery->first()->imaginator_sources as $imaginatorSource) {
			$sourcePath = str_replace('//', '/', public_path($imaginatorSource->source));
			$resizedPath = str_replace('//', '/', public_path($imaginatorSource->resized));

			if (!File::exists($resizedPath)) {
				$imaginatorSource->resized = null;
				$imaginatorSource->save();
			}

			if (File::exists($sourcePath) && !File::isDirectory($sourcePath)) {
				continue;
			}

			$imaginatorSource->delete();
		}

		return $imaginatorQuery->first();
	}
}

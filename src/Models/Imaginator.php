<?php

namespace Bistroagency\Imaginator\Models;

use Bistroagency\Imaginator\ImaginatorLogic;
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

	protected $imaginators;

	public function getImaginators($fresh = false)
	{
		if($fresh) {
			$this->imaginators = app('ImaginatorRepository')->fresh();
		}

		if ($this->imaginators !== null) {
			return $this->imaginators;
		}

		$this->imaginators = app('ImaginatorRepository')->get();

		return $this->imaginators;
	}

	public function imaginator_template()
	{
		return $this->belongsTo(ImaginatorTemplate::class)->with('imaginator_variations');
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
				$breakpoint = config('imaginator.breakpoints.default')[$variations[$i]->breakpoint];
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

		if ($imaginatorTemplate) {
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

		if (!$imaginatorQuery->first()) {
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

	public static function getImaginatorSrcsetSizes()
	{
		$breakpointSizes = config('imaginator.breakpoints.default_sizes');
		$sizes = [];
		foreach ($breakpointSizes as $breakpointName => $breakpointSize) {
			$sizes[$breakpointName] = '(min-width: ' . $breakpointSize . ')';
		}
		return $sizes;
	}

	public static function getImaginator($aliasOrId)
	{
		if (!is_string($aliasOrId)) {
			return (new static)->getImaginators()->find($aliasOrId);
		}

		return (new static)->getImaginators()->where('alias', $aliasOrId)->first();
	}

	public static function generateImaginatorPicture($imaginator, string $locale = null, array $attributes = [])
	{
		if($imaginator->imaginator_sources->count() < 1) {
			throw new \Exception('Imaginator without sources');
		}
		//check if supplied attributes are allowed on the picture tag
		self::checkAllowedPictureAttributes($attributes);
		$appendableAttributes = [];

		if (!$locale) {
			$locale = locale();
		}

		foreach ($attributes as $attributeKey => $attribute) {
			if (!isset($attributes[$attributeKey]) || !is_string($attribute)) {
				$attributes[$attributeKey] = null;
			}
			$appendableAttributes[] = $attributeKey . '="' . $attribute . '"';
		}

		//prepare picture opening tag
		if (!isset($html)) {
			$html = '<picture ' . implode(' ', $appendableAttributes) . '>';
		}

		//get srcset sizes and imaginator
		$srcsetSizes = self::getImaginatorSrcsetSizes();

		//get lazyload array
		$lazyloadArray = json_decode($imaginator->getLazyloadObject($locale), true);

		//prepare picture sources and html markup
		foreach ($lazyloadArray as $breakpoint => $lazyloadImage) {
			$sources = [];
			foreach ($lazyloadImage as $imageKey => $image) {
				if ($imageKey === 'retina') {
					$sources[] = url($image) . ' 2x';
					continue;
				}
				$sources[] = url($image) . ' 1x';
			}
			$html .= '<source srcset="' . implode(',', $sources) . '" media="' . $srcsetSizes[$breakpoint] . '">';
		}
		//use srcset instead of src on img tag because of polyfill compatibility
		$html .= '<img srcset="' . url($imaginator->imaginator_sources[0]->source) . '" alt="' . $imaginator->id . '">';
		$html .= '</picture>';

		//return one picture
		return $html;
	}

	public static function getOrCreateImaginator($aliasOrIdOrPath, string $templateName, string $anchorPoint)
	{
		$template = ImaginatorTemplate::where('name', $templateName)->firstOrFail();

		return ImaginatorLogic::getOrCreateImaginator($aliasOrIdOrPath, $template, $anchorPoint);
	}

	protected static function checkAllowedPictureAttributes(array $attributes = [])
	{
		foreach ($attributes as $attributeKey => $attribute) {
			if (!in_array($attributeKey, config('imaginator.app.allowedPictureAttributes'))) {
				throw new \Exception('Unallowed attribute', 500);
			}
		}

		return true;
	}
}

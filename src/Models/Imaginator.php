<?php

namespace Bistroagency\Imaginator\Models;

use Bistroagency\Imaginator\ImaginatorController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Imaginator extends Model
{
	use SoftDeletes;

	/** @var Collection */
	protected $imaginators;

	protected $fillable = [
		'alias',
		'imaginator_template_id',
		'imaginatorable_id',
		'imaginatorable_type',
	];

	protected $attributes = [
		'imaginator_template_id' => null,
		'alias' => null,
	];

	protected $casts = [
		'id' => 'integer',
		'imaginator_template_id' => 'integer',
		'imaginatorable_id' => 'integer',
	];

	/*
	 * Relationships
	 */

	public function imaginatorable(): MorphTo
	{
		return $this->morphTo();
	}

	public function imaginator_template(): BelongsTo
	{
		return $this->belongsTo(ImaginatorTemplate::class)->with('imaginator_variations');
	}

	public function imaginator_sources(): HasMany
	{
		return $this->hasMany(ImaginatorSource::class);
	}

	/*
	 * Functions
	 */

	public function getImaginators($fresh = false)
	{
		if ($fresh) {
			$this->imaginators = app('ImaginatorRepository')->fresh();
		}

		if ($this->imaginators !== null) {
			return $this->imaginators;
		}

		$this->imaginators = app('ImaginatorRepository')->get();

		return $this->imaginators;
	}

	public function getLazyloadObject($locale = null)
	{
		if (!$this->exists) {
			return json_encode([]);
		}

		$lazyArray = [];
		$variations = $this->imaginator_template->imaginator_variations;

		for ($i = 0; $i < $variations->count(); $i++) {

			if ($variations[$i]->locale === 'all' || $variations[$i]->locale === $locale) {

				$breakpoint = config('imaginator.breakpoints.default')[$variations[$i]->breakpoint];
				$density = $variations[$i]->density;

				if (isset($this->imaginator_sources[$i])) {
					$lazyArray[$breakpoint][$density] = imaginator_asset_versioned($this->imaginator_sources[$i]->resized);
				}

			}

		}

		return json_encode($lazyArray);
	}

	public function isUsed(): bool
	{
		return false;
	}

	public function getPreviewImageUrl()
	{
		$firstSource = $this->imaginator_sources->first();

		return ($firstSource && $firstSource->source !== '')
			? url($firstSource->source)
			: null;
	}

	public function getVariations(): array
	{
		$imagesByVariations = [];
		$variations = $this->imaginator_template->imaginator_variations;

		for ($i = 0; $i < $variations->count(); $i++) {
			if ($variations[$i]->locale === 'all' || $variations[$i]->locale === locale()) {
				$breakpoint = config('imaginator.breakpoints.default')[$variations[$i]->breakpoint];
				$density = $variations[$i]->density;

				if (isset($this->imaginator_sources[$i])) {
					$imagesByVariations[$breakpoint][$density] = url(imaginator_asset_versioned($this->imaginator_sources[$i]->resized));
				}
			}
		}

		return $imagesByVariations;
	}

	/*
	 * Static
	 */

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
		if ($aliasOrId instanceof self) {
			return $aliasOrId;
		}

		if (!is_string($aliasOrId)) {
			return (new static)->getImaginators()->find($aliasOrId);
		}

		return (new static)->getImaginators()->where('alias', $aliasOrId)->first();
	}

	public static function generateImaginatorPicture($imaginator, string $locale = null, array $attributes = [])
	{
		$imaginator = self::getImaginator($imaginator);

		if (!$imaginator) {
			throw new \Exception('Cannot find Imaginator.');
		}
		if ($imaginator->imaginator_sources->count() < 1) {
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
		$html = '<picture ' . implode(' ', $appendableAttributes) . '>';

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

	public static function getOrCreateImaginator($resources, string $templateName, string $anchorPoint)
	{
		$template = ImaginatorTemplate::where('name', $templateName)->firstOrFail();

		return ImaginatorController::getOrCreateImaginator($resources, $template, $anchorPoint);
	}

	protected static function checkAllowedPictureAttributes(array $attributes = [])
	{
		foreach ($attributes as $attributeKey => $attribute) {
			if (!in_array($attributeKey, config('imaginator.app.allowedPictureAttributes'), true)) {
				throw new \Exception('Unallowed attribute', 500);
			}
		}

		return true;
	}
}

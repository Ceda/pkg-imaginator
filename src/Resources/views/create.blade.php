@extends('imaginator::layout.imaginator')

@section('title', 'Imaginator')

@section('content')
	<div class="ui centered grid imaginator-create" style="padding-top: 40px;" v-cloak>
		<div class="row">
			<div class="fourteen wide column">
				<div class="ui tabular menu">
					<a class="item
					   @if(Route::current()->getName() == config('imaginator.app.routes.as').'create') active @endif">
						<i class="plus icon"></i>
						Přidat
					</a>
					<a class="item"
					   href="{{ $imaginatorsViewUrl }}">
						<i class="list icon"></i>
						Přehled
					</a>
				</div>
			</div>
		</div>
		<div class="eight wide column">
			<div class="imaginator-create__headline">
				<strong>@{{ template.label }}</strong><span v-if="template.description"> - @{{ template.description }}</span>
			</div>
			<div class="ui segment imaginator-create__dropzone" data-url="{{ route('imaginator.upload') }}">
				<div class="imaginator-create__thumbnail-wrap"
					 :class="{'is-pointable': hasAllSourcesOrImaginatorHasId}"
				>
					<div class="imaginator-create__thumbnail-text" v-if="!hasAllSources && !imaginator.id">
						Nahrajte<br>obrázek
					</div>

					<div class="imaginator-create__thumbnail-item"
						 v-for="variation in variations"
						 v-if="hasAllSourcesOrImaginatorHasId"
						 :style="{ width: thumbnailDimensions.width}"
					>
						<div class="imaginator-create__thumbnail-item-name">
							@{{ variation.name }}
							<i class="crop icon imaginator-create__thumbnail-crop-open"
							   v-if="variation.isSelected && !variation.isResizing && !hasSomeWithoutSources && !hasAllWithoutSources"
							   :class="['crop-open--' + variation.id]"
							   @click="setCroppie(variation, $event)"
							   style="font-size:20px"
							></i>
							<div class="ui mini teal icon button imaginator-create__thumbnail-crop-submit"
								 :class="['crop-submit--' + variation.id]"
								 v-if="variation.isResizing && !variation.isResizeLoading"
								 @click="getCroppedImage(variation)"
							>
								<i class="check icon"></i>
							</div>
						</div>
						<div class="imaginator-create__thumbnail-item-image"
							 :class="['thumbnail-image--' + variation.id, {'is-selected': variation.isSelected}]"
							 @click="selectVariation(variation, $event)"
						>
							<img :src="sourceToShow(variation)" :class="['image-to-crop--' + variation.id]" alt="">
							<div class="imaginator-create__thumbnail-item-image-text"
								 v-if="variation.hasNoSource"
							>
								Variace nemá žádný zdroj<br>
								Zvolte obrázek pro pokračování
							</div>
						</div>
						<div :class="{'active': variation.isResizeLoading}"
							 class="ui big inverted dimmer"
							 style="height:calc(100% - 35px);top:35px!important;"
						>
							<div class="ui loader"></div>
						</div>
					</div>
				</div>
				<div :class="{'active': !allThumbnailsShown && hasAllSources}" class="ui big inverted dimmer imaginator-create__dropzone-loader">
					<div class="ui loader"></div>
				</div>
			</div>
		</div>
		<div class="four wide column" style="padding-top: 47px;">
			<div class="ui container">
				<div class="imaginator-create__variations">
					<div class="ui right aligned container" style="margin-bottom: 15px">
						<div class="imaginator-create__select">
							<button
								class="imaginator-create__select-all"
								type="button"
								:class="{'disabled': !hasAllSources}"
								@click.prevent="selectAllVariations"
							>
								<i class="checkmark box icon"></i>
								Vybrat všechny
							</button>

							<button
								class="imaginator-create__select-none"
								type="button"
								:class="{'disabled': !hasAllSources}"
								@click.prevent="deselectAllVariations"
								v-if="isSomethingSelected && hasAllSources"
							>
								<i class="remove box icon"></i>
							</button>
						</div>
					</div>
					<div v-for="variation in variations">
						<div class="ui large basic fluid button imaginator-create__variation"
							 style="margin-bottom:15px;text-align:left;position:relative;line-height:1.5em"
							 @click="selectVariation(variation)"
							 :id="variation.id"
							 :class="{'teal': variation.isSelected, 'disabled': !hasAllSources || hasSomeWithoutSources}"
						>
							<i class="square outline icon" :class="{'check': variation.isSelected}"></i>
							@{{ variation.name }}
							<div class="variation-size">
								Finální velikost: @{{ variation.width }}&times;@{{ variation.height }}
							</div>
						</div>
					</div>

					<button class="fluid large teal ui button"
							@click="triggerOpenDropzone"
							v-if="hasAllSourcesOrImaginatorHasId && !isSomethingResizing"
							:class="{'disabled': !isSomethingSelected}"
							style="text-align:left; margin-bottom:15px;">
						<i class="folder open icon"></i>
						Změnit soubor pro @{{ numberOfSelected }} @{{ numberOfSelected > 1 ? 'variace' : 'variaci' }}
					</button>

					<button class="fluid large teal ui button"
							@click="getAllCroppedImages"
							v-if="isSomethingResizing"
							style="text-align:left;">
						<i class="check icon icon"></i>
						Ořezat vybrané variace
					</button>

				</div>
			</div>
		</div>
		<div class="row">
			<div class="sixteen wide column center aligned">
				<button
						class="large positive ui button"
						:class="{'disabled': !hasAllSources || isSomethingResizing}"
						style="margin-top:30px;"
						@click="sendFile">
					Uložit a zavřít
				</button>
			</div>
		</div>
		<div class="ui inverted dimmer" :class="{'active': isSaving}">
			<div class="ui text large loader">Ukládám</div>
		</div>
	</div>

@endsection

@section('scripts')
	<script src="{{ asset_versioned('assets/imaginator/dist/js/imaginator-create.js') }}"></script>
	<script>
		ImaginatorCreate.templateFromPHP = JSON.parse('{!! addslashes(json_encode($imaginatorTemplate->toArray())) !!}');
		ImaginatorCreate.variationsFromPHP = JSON.parse('{!! addslashes(json_encode($imaginatorTemplate->imaginator_variations->toArray())) !!}');
		ImaginatorCreate.imaginatorFromPHP = JSON.parse('{!! addslashes(json_encode($imaginator)) !!}');
		ImaginatorCreate.sourcesFromPHP = JSON.parse('{!! addslashes(json_encode($imaginatorSources)) !!}');

		$(document).ready(function () {
			ImaginatorCreate.init();
		});
	</script>
@endsection

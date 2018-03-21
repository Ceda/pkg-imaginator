@extends('imaginator::layout.imaginator')

@section('title', 'Seznam Imaginátorů pro '.$imaginatorTemplate->label)

@php
	$locale = locale();
@endphp

@section('content')

	<div class="ui centered grid imaginator-view" style="padding-top: 40px;" v-cloak>
		<div class="row">
			<div class="fourteen wide column">
				<div class="ui tabular menu">
					<a class="item"
					   href="{{ $imaginatorCreateUrl }}">
						<i class="plus icon"></i>
						Přidat
					</a>
					<a class="item
					   @if(Route::current()->getName() == config('imaginator.app.routes.as').'view') active @endif">
						<i class="list icon"></i>
						Přehled
					</a>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="fourteen wide column">
				<div class="ui small images">
					@foreach($imaginators as $imaginator)
						<div class="imaginator-view__item">
							@if(!$imaginator->imaginator_sources->count() && !$imaginator->getPreviewImageUrl())
								<a href="#"
								   class="imaginator-view__item-image"
								   @click.prevent="exitLightbox({{ $imaginator->id }})"
								>
									<div class="imaginator-view__item-placeholder"></div>
								</a>
							@else
								<a href="#"
								   class="imaginator-view__item-image"
								   @click.prevent="exitLightbox({{ $imaginator->id }})"
								>
									<img src="{{ $imaginator->getPreviewImageUrl() }}" style="width:auto;">
								</a>
							@endif
							<div class="imaginator-view__item-name">
								<a href="{{ $imaginatorCreateUrl }}?imaginator={{ $imaginator->id }}">
									{{ $imaginator->imaginator_template->label }} {{ $imaginator->id }}
									<i class="edit icon"></i>
								</a>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		</div>

		@if (!count($imaginators))
			<div class="row">
				<div class="fourteen wide column">
					<div class="ui yellow message">
						<div class="content">
							<div class="header">
								Dosud není vytvořen žádny Imaginator.
							</div>
							<p>
								Nový Imaginator vytvoříte klinutím <a href="{{$imaginatorCreateUrl}}">zde</a>.
							</p>
						</div>
					</div>
				</div>
			</div>
		@endif
		{{ $imaginators->render('imaginator::partials.pagination.default') }}
	</div>
@endsection

@include('imaginator::partials.modal.delete')

@section('scripts')
	<script src="{{ asset_versioned('assets/imaginator/dist/js/imaginator-view.js') }}"></script>
	<script>
		ImaginatorView.init();
	</script>
@endsection

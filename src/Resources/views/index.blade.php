@extends('imaginator::layout.imaginator')

@section('title', 'Seznam Imaginátorů')

@section('content')

  <div class="ui fluid center aligned container">
    <div class="ui grid">
      <div class="fourteen wide centered column">
        <div class="ui grid">
          <div class="ui column">
            <div class="ui right aligned secondary segment">
              <div class="text">
                Seznam Imaginátorů
              </div>
              <form method="POST"
                  action="{{ route('imaginator.destroy.allUnused') }}"
                  class="table--delete-form"
                  data-delete-name="všechny nepoužité Imaginátory">
                <input type="hidden" name="_method" value="DELETE">
                <div class="ui buttons">
                  <div class="ui labeled small icon top left pointing dropdown green button">
                    <i class="plus icon"></i>
                    Přidat
                    <div class="menu">
                      @foreach($templates as $template)
                        <a class="item"
                           href="{{ route('imaginator.create', $template->name) }}">
                          {{ $template->label }}
                        </a>
                      @endforeach
                    </div>
                  </div>
                  <button type="button" class="ui labeled small red icon button"
                      data-position="top left"
                      data-delete-item>
                    <i class="trash icon"></i>
                    Smazat nepoužité
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <table class="ui small selectable striped table">
          <thead>
          <tr>
            <th class="table--delete-action"></th>
            <th class="table--thumbnail">Náhled</th>
            <th>#</th>
            <th>Template</th>
            <th>Popis</th>
            <th class="table--info">Info</th>
          </tr>
          </thead>
          <tbody>
          @foreach($imaginators as $imaginator)
            <tr>
              <td class="table--delete-action">
                @if($imaginator->isUsed())
                  <button type="button" class="ui small icon button tooltip"
                      data-content="Imaginátor nejde smazat protože se někde používá">
                    <i class="lock icon"></i>
                  </button>
                @else
                  <form method="POST" class="table--delete-form"
                      action="{{ route('imaginator.destroy', $imaginator->id) }}"
                      data-delete-name="Imaginátor {{ $imaginator->imaginator_template->label }} {{ $imaginator->id }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="ui small red icon button"
                        data-delete-item>
                      <i class="trash icon"></i>
                    </button>
                  </form>
                @endif
              </td>
              <td class="table--thumbnail">
                @if ($imaginator->imaginator_sources->count() > 0)
                  <img src="{{ $imaginator->getPreviewImageUrl() }}"
                     alt=""
                     class="table--thumbnail-img">
                @else
                  <img src="{{ dummy_image(85, 40) }}"
                     alt=""
                     class="table--thumbnail-img">
                @endif
              </td>
              <td>{{ $imaginator->id }}</td>
              <td><a href="{{ route('imaginator.create', ['template' => $imaginator->imaginator_template->name, 'imaginator' => $imaginator->id]) }}" class="ui link">{{ $imaginator->imaginator_template->label }}</a></td>
              <td>{{ strlen($imaginator->imaginator_template->description) ? $imaginator->imaginator_template->description : 'Není popis' }}</td>
              <td class="table--info">
                <button
                  type="button"
                  class="ui blue label tooltip"
                  data-content="
                  @foreach($imaginator->imaginator_template->imaginator_variations as $variationKey => $variation)
                  {{ $imaginator->imaginator_template->imaginator_variations->count() - 1 !== $variationKey ? $variation->name.',' : $variation->name }}
                  @endforeach
                    ">
                  {{ $imaginator->imaginator_template->imaginator_variations->count() }} variace
                </button>

              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
        {{ $imaginators->links('imaginator::partials.pagination.default') }}
      </div>
    </div>
  </div>

@endsection

@include('imaginator::partials.modal.delete')

@section('scripts')
  <script src="{{ asset_versioned('assets/imaginator/dist/js/imaginator-create.js') }}"></script>
  <script>
    ImaginatorCreate.init();
  </script>
@endsection

@extends('imaginator::layout.imaginator')

@section('title', 'Šablony Imaginátorů')


@section('content')
<div class="ui one column centered middle aligned grid">
  <div class="row">
    <div class="four wide column">
      <div class="ui fluid secondary message">
        <p>Šablony Imaginátorů</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="four wide column">
      <div class="ui fluid vertical menu">
        @foreach($imaginatorTemplates as $template)
          <a href="{{ route('imaginator.create', $template->name) }}" class="item">
            {{ $template->label }}
          </a>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
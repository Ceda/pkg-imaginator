<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="robots" content="noindex, nofollow">

  <link rel="apple-touch-icon" sizes="180x180" href="{{ url('assets/favicons/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ url('assets/favicons/favicon-32x32.png') }}" sizes="32x32">
  <link rel="icon" type="image/png" href="{{ url('assets/favicons/favicon-16x16.png') }}" sizes="16x16">
  <link rel="manifest" href="{{ url('assets/favicons/manifest.json') }}">
  <link rel="mask-icon" href="{{ url('assets/favicons/safari-pinned-tab.svg') }}" color="#5bbad5">
  <meta name="theme-color" content="#ffffff">

  <title>@yield('title') - Imaginator</title>

  <link href="{{ asset_versioned('assets/imaginator/semantic/semantic.css') }}" rel="stylesheet">
  <link href="{{ asset_versioned('assets/imaginator/dist/css/parts.css') }}" rel="stylesheet">

  <script>
    var IMAGINATOR = {
      uploadUrl: '{{ route('imaginator.upload') }}',
      storeUrl: '{{ route('imaginator.store') }}',
      appUrl: '{{ url('/') }}',
      csrf_token: '{{ csrf_token() }}',
    };
  </script>
</head>

<body class="{{ body_class() }}">

<div class="content">
  <div class="ui grid">
    <div class="fourteen wide centered column">
      @include('imaginator::partials.messages')
    </div>
  </div>

  @yield('content')
</div>

<script src="{{ asset_versioned('assets/imaginator/jquery/jquery.min.js') }}"></script>
<script src="{{ asset_versioned('assets/imaginator/dist/js/libs-admin.js') }}"></script>
<script src="{{ asset_versioned('assets/imaginator/semantic/semantic.js') }}"></script>
@yield('scripts')
</body>
</html>

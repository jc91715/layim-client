<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css','build/front') }}" rel="stylesheet">
    <link href="{{ mix('css/front.css','build/front') }}" rel="stylesheet">
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    @yield('styles')
</head>
<body style="font-size: 16px">
<div id="app">
    @yield('content')
</div>

<script src="{{ mix('js/manifest.js','build/front') }}"></script>
<script src="{{ mix('js/vendor.js','build/front')}}"></script>
<script src="{{ mix('js/bootstrap.js','build/front')}}"></script>
<script src="{{ mix('js/front.js','build/front')}}"></script>

@yield('scripts')
<script>

</script>
</body>
</html>

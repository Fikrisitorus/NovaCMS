<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'NovaCMS'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name', 'NovaCMS'))">

    @if (!empty($seoImage))
        <meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', config('app.name', 'NovaCMS'))">
    <meta property="og:description" content="@yield('meta_description', config('app.name', 'NovaCMS'))">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
    @livewireScripts
</body>
</html>

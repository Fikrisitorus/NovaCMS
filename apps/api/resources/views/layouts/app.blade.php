<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'NovaCMS - Modern Headless CMS & Visual Website Builder')">

    <title>@yield('title', 'NovaCMS')</title>

    <!-- Preconnect untuk performa font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-950 text-white antialiased font-sans min-h-screen flex flex-col">

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center font-bold text-sm transition-transform group-hover:scale-110">
                        N
                    </div>
                    <span class="text-lg font-bold bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                        NovaCMS
                    </span>
                </a>

                {{-- Navigation Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">
                        Home
                    </a>
                    <a href="{{ route('blog.index') }}" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">
                        Blog
                    </a>
                </div>

                {{-- CTA Button --}}
                <div class="flex items-center gap-4">
                    <a href="/admin" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all duration-300 hover:scale-105">
                        Dashboard
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1 pt-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-white/5 bg-slate-950">
        <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-md bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center font-bold text-xs">
                        N
                    </div>
                    <span class="text-sm font-semibold text-slate-400">NovaCMS</span>
                </div>
                <p class="text-sm text-slate-500">
                    &copy; {{ date('Y') }} NovaCMS. Built with ❤️ using Laravel.
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

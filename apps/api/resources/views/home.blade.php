@extends('layouts.app')

@section('title', 'NovaCMS - Modern Headless CMS')
@section('meta_description', 'Build, manage, and publish modern websites through a flexible component-based architecture.')

@section('content')
    {{-- Hero Section --}}
    <section class="relative overflow-hidden">
        {{-- Background Gradient Effects --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 h-96 w-96 rounded-full bg-fuchsia-600/20 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-64 w-64 rounded-full bg-indigo-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:py-40 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                {{-- Badge --}}
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-violet-500/20 bg-violet-500/10 px-4 py-1.5 text-sm text-violet-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-violet-400"></span>
                    </span>
                    Currently under active development
                </div>

                {{-- Heading --}}
                <h1 class="text-5xl font-bold tracking-tight sm:text-7xl">
                    <span class="bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
                        Modern Headless
                    </span>
                    <br>
                    <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent">
                        CMS Platform
                    </span>
                </h1>

                {{-- Subtitle --}}
                <p class="mt-6 text-lg leading-8 text-slate-400 sm:text-xl">
                    Build, manage, and publish modern websites through a flexible
                    <span class="text-white font-medium">component-based architecture.</span>
                    Powered by Laravel & Filament.
                </p>

                {{-- CTA Buttons --}}
                <div class="mt-10 flex items-center justify-center gap-4">
                    <a href="/admin" class="rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all duration-300 hover:scale-105">
                        Open Dashboard
                    </a>
                    <a href="{{ route('blog.index') }}" class="rounded-full border border-white/10 bg-white/5 px-8 py-3 text-sm font-semibold text-white backdrop-blur-sm hover:bg-white/10 transition-all duration-300">
                        Read Blog
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="relative border-t border-white/5">
        <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                    Everything You Need
                </h2>
                <p class="mt-4 text-lg text-slate-400">
                    Powerful features to build and manage your websites with ease.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Feature Cards --}}
                @php
                    $features = [
                        ['icon' => '🌐', 'title' => 'Multi Website', 'desc' => 'Manage multiple websites from a single dashboard with isolated content and settings.'],
                        ['icon' => '📄', 'title' => 'Dynamic Pages', 'desc' => 'Create pages with drag-and-drop section builder. Hero, FAQ, Gallery, and more.'],
                        ['icon' => '✍️', 'title' => 'Blog System', 'desc' => 'Built-in blog with rich editor, scheduling, and SEO optimization tools.'],
                        ['icon' => '🔗', 'title' => 'REST API', 'desc' => 'Headless-first architecture. Consume content via clean, versioned JSON API.'],
                        ['icon' => '🎨', 'title' => 'Content Builder', 'desc' => 'Visual section builder with pre-built components: Hero, Pricing, CTA, Team, and more.'],
                        ['icon' => '🔒', 'title' => 'Role & Permission', 'desc' => 'Fine-grained access control with role-based permissions for your team.'],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/[0.02] p-8 transition-all duration-300 hover:border-violet-500/20 hover:bg-white/[0.04]">
                        <div class="absolute inset-0 bg-gradient-to-br from-violet-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative">
                            <div class="text-3xl mb-4">{{ $feature['icon'] }}</div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ $feature['title'] }}</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Tech Stack Section --}}
    <section class="relative border-t border-white/5">
        <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                    Tech Stack
                </h2>
                <p class="mt-4 text-lg text-slate-400">
                    Built with modern, battle-tested technologies.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                @php
                    $techs = ['Laravel 12', 'PHP 8.4', 'Filament v3', 'PostgreSQL', 'Livewire', 'TailwindCSS', 'Alpine.js', 'Vite'];
                @endphp

                @foreach ($techs as $tech)
                    <span class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-sm font-medium text-slate-300 hover:border-violet-500/30 hover:text-white transition-all duration-200">
                        {{ $tech }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>
@endsection

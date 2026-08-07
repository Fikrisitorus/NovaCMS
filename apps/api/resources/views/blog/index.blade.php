@extends('layouts.app')

@section('title', 'Blog - NovaCMS')
@section('meta_description', 'Baca artikel terbaru tentang pengembangan web, teknologi, dan tips dari tim NovaCMS.')

@section('content')
    <section class="relative">
        {{-- Background Gradient --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-1/4 h-96 w-96 rounded-full bg-violet-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
            {{-- Page Header --}}
            <div class="mx-auto max-w-2xl text-center mb-16">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                    Blog
                </h1>
                <p class="mt-4 text-lg text-slate-400">
                    Insights, tutorials, and updates from the NovaCMS team.
                </p>
            </div>

            {{-- Blog Grid --}}
            @if ($posts->count() > 0)
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/[0.02] transition-all duration-300 hover:border-violet-500/20 hover:bg-white/[0.04]">
                            {{-- Gradient overlay on hover --}}
                            <div class="absolute inset-0 bg-gradient-to-b from-violet-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="relative p-8">
                                {{-- Date --}}
                                <time class="text-xs font-medium text-violet-400 uppercase tracking-wider">
                                    {{ $post->published_at->format('d M Y') }}
                                </time>

                                {{-- Title --}}
                                <h2 class="mt-3 text-xl font-bold text-white group-hover:text-violet-300 transition-colors duration-200">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                {{-- Excerpt --}}
                                <p class="mt-3 text-sm text-slate-400 leading-relaxed line-clamp-3">
                                    {{ Str::limit(strip_tags($post->content), 150) }}
                                </p>

                                {{-- Read More --}}
                                <div class="mt-6">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center gap-1 text-sm font-medium text-violet-400 hover:text-violet-300 transition-colors">
                                        Read more
                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-16">
                    <div class="mx-auto h-16 w-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                        <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-300">No posts yet</h3>
                    <p class="mt-2 text-sm text-slate-500">Check back later for new content.</p>
                </div>
            @endif
        </div>
    </section>
@endsection

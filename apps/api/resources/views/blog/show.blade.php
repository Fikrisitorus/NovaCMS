@extends('layouts.app')

@section('title', $post->title . ' - NovaCMS Blog')
@section('meta_description', Str::limit(strip_tags($post->content), 160))

@section('content')
    <article class="relative">
        {{-- Background Gradient --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/3 h-96 w-96 rounded-full bg-violet-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-3xl px-6 py-24 lg:px-8">
            {{-- Back Link --}}
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors mb-8">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to Blog
            </a>

            {{-- Article Header --}}
            <header class="mb-12">
                <time class="text-sm font-medium text-violet-400 uppercase tracking-wider">
                    {{ $post->published_at->format('d F Y') }}
                </time>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">
                    {{ $post->title }}
                </h1>
            </header>

            {{-- Article Content --}}
            <div class="prose prose-invert prose-lg max-w-none
                        prose-headings:bg-gradient-to-r prose-headings:from-white prose-headings:to-slate-300 prose-headings:bg-clip-text prose-headings:text-transparent
                        prose-p:text-slate-300 prose-p:leading-relaxed
                        prose-a:text-violet-400 prose-a:no-underline hover:prose-a:text-violet-300
                        prose-strong:text-white
                        prose-code:text-fuchsia-300 prose-code:bg-white/5 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded-md
                        prose-blockquote:border-violet-500/50 prose-blockquote:text-slate-400
                        prose-li:text-slate-300">
                {!! $post->content !!}
            </div>

            {{-- Separator --}}
            <div class="mt-16 border-t border-white/5 pt-8">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-6 py-2.5 text-sm font-medium text-white hover:bg-white/10 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    All Posts
                </a>
            </div>
        </div>
    </article>
@endsection

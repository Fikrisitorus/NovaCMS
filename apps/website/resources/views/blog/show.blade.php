@extends('layouts.app')

@section('title', $post['seo_meta']['meta_title'] ?? $post['title'])
@section('meta_description', $post['seo_meta']['meta_description'] ?? ($post['excerpt'] ?? ''))

@section('content')
    <article class="max-w-3xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('blog.index') }}" class="text-indigo-600 hover:underline text-sm">
            &larr; Kembali ke Blog
        </a>

        @if (! empty($post['categories']))
            <div class="flex gap-2 mt-4 mb-3">
                @foreach ($post['categories'] as $category)
                    <a
                        href="{{ route('categories.show', $category['slug']) }}"
                        class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded hover:bg-indigo-100"
                    >
                        {{ $category['name'] }}
                    </a>
                @endforeach
            </div>
        @endif

        <h1 class="text-4xl font-bold mb-4">{{ $post['title'] }}</h1>

        <div class="flex items-center gap-3 text-sm text-gray-500 mb-8">
            @if (! empty($post['author']['name']))
                <span>{{ $post['author']['name'] }}</span>
                <span>&middot;</span>
            @endif
            @if (! empty($post['published_at']))
                <time>{{ \Illuminate\Support\Carbon::parse($post['published_at'])->translatedFormat('d M Y') }}</time>
            @endif
        </div>

        @if (! empty($post['featured_image']))
            <img
                src="{{ $post['featured_image'] }}"
                alt="{{ $post['title'] }}"
                class="w-full h-64 md:h-96 object-cover rounded-lg mb-8"
            >
        @endif

        <div class="prose prose-lg max-w-none">
            {!! $post['content'] ?? '' !!}
        </div>
    </article>
@endsection

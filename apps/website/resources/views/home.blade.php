@extends('layouts.app')

@section('title', $page['seo_meta']['meta_title'] ?? ($page['title'] ?? 'Beranda'))

@section('content')
    {{-- Render blocks halaman dinamis (hero, faq, gallery, pricing, contact) --}}
    @foreach ($blocks as $block)
        @if (view()->exists("blocks.{$block['type']}"))
            @include("blocks.{$block['type']}", ['data' => $block['data'] ?? []])
        @endif
    @endforeach

    {{-- Section post terbaru --}}
    <section class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Post Terbaru</h2>
            <a href="{{ route('blog.index') }}" class="text-indigo-600 hover:underline">
                Lihat semua &rarr;
            </a>
        </div>

        @if ($posts->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Belum ada post yang dipublikasikan.</p>
        @endif
    </section>
@endsection

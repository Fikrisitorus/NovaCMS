@extends('layouts.app')

@section('title', 'Blog')
@section('meta_description', 'Semua post terbaru dari NovaCMS.')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-8">Blog</h1>

        @if ($posts->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>

            {{-- Pagination --}}
            @if (! empty($meta) && ($meta['last_page'] ?? 1) > 1)
                <nav class="mt-10 flex justify-center gap-2" aria-label="Pagination">
                    @for ($i = 1; $i <= $meta['last_page']; $i++)
                        <a
                            href="{{ route('blog.index', ['page' => $i]) }}"
                            class="px-4 py-2 rounded border text-sm {{
                                $i === ($meta['current_page'] ?? 1)
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-indigo-600'
                            }}"
                        >
                            {{ $i }}
                        </a>
                    @endfor
                </nav>
            @endif
        @else
            <p class="text-gray-500">Belum ada post yang dipublikasikan.</p>
        @endif
    </section>
@endsection

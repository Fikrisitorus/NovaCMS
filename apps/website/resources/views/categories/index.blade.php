@extends('layouts.app')

@section('title', 'Kategori')
@section('meta_description', 'Jelajahi semua kategori post di NovaCMS.')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-8">Kategori</h1>

        @if ($categories->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('categories.show', $category['slug']) }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 hover:shadow-md hover:border-indigo-300 transition"
                    >
                        <h3 class="text-lg font-semibold mb-2">{{ $category['name'] }}</h3>
                        @if (! empty($category['description']))
                            <p class="text-gray-600 text-sm">{{ $category['description'] }}</p>
                        @endif
                        @if (! empty($category['posts_count']))
                            <p class="mt-3 text-xs text-indigo-600 font-medium">
                                {{ $category['posts_count'] }} post
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Belum ada kategori.</p>
        @endif
    </section>
@endsection

{{--
    Halaman kontak publik: info penerima + form pengiriman pesan.
--}}
@extends('layouts.app')

@section('title', 'Kontak')
@section('meta_description', $description ?? 'Hubungi tim NovaCMS.')

@section('content')
    <section class="max-w-3xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-center mb-6">{{ $title ?? 'Kontak' }}</h1>

        @if (! empty($description))
            <p class="text-gray-600 text-center mb-10 leading-relaxed">{{ $description }}</p>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-8">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    class="mb-6 flex items-start justify-between gap-3 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800"
                >
                    <span>{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-600 hover:text-green-800" aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <livewire:contact-form />
        </div>
    </section>
@endsection

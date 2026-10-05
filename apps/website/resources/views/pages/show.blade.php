@extends('layouts.app')

@section('title', $seoTitle ?? ($page['title'] ?? 'Halaman'))

@section('content')
    @foreach ($blocks as $block)
        @if (view()->exists("blocks.{$block['type']}"))
            @include("blocks.{$block['type']}", ['data' => $block['data'] ?? []])
        @endif
    @endforeach

    @if (empty($blocks) && ! empty($page['title']))
        <section class="max-w-3xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">{{ $page['title'] }}</h1>
        </section>
    @endif
@endsection

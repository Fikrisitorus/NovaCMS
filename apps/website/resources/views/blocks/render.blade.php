@php
    /**
     * Merender daftar blocks dari kolom JSON Page (API NovaCMS).
     *
     * Setiap item berformat: { type: 'hero'|'faq'|'gallery'|'pricing'|'contact', data: {...} }
     * dan dipetakan ke partial blade pada resources/views/blocks/<type>.blade.php.
     * Tipe tidak dikenal dilewati agar satu block rusak tidak menghempaskan
     * seluruh halaman.
     */
    $blocks = $blocks ?? [];
@endphp

@foreach ($blocks as $block)
    @php
        $type = is_array($block) ? ($block['type'] ?? null) : null;
        $data = is_array($block) ? ($block['data'] ?? []) : [];
    @endphp

    @if ($type !== null && view()->exists('blocks.'.$type))
        @include('blocks.'.$type, ['data' => $data])
    @endif
@endforeach

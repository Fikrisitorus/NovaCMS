{{--
    Meta SEO yang dipasang dari data API (seo_meta pada Post/Page).
    Dipakai lewat @section di layout: title, meta_description,
    canonical_url, keywords, dan open graph image.
--}}
@push('head')
    @isset($canonicalUrl)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endisset
    @isset($keywords)
        <meta name="keywords" content="{{ is_array($keywords) ? implode(', ', $keywords) : $keywords }}">
    @endisset
    @isset($seoImage)
        <meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @endisset
@endpush

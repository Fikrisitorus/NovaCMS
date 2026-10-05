{{--
    Block tipe "hero": heading, subheading, dan tombol CTA.
    Data: { heading, subheading, background_image }
--}}
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white">
    @if (! empty($data['background_image']))
        <img src="{{ $data['background_image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30">
    @endif

    <div class="relative max-w-7xl mx-auto px-4 py-20 sm:px-6 lg:px-8 lg:py-28 text-center">
        @if (! empty($data['title']))
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">{{ $data['title'] }}</h1>
        @endif

        @if (! empty($data['heading']))
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">{{ $data['heading'] }}</h1>
        @endif

        @if (! empty($data['subheading']))
            <p class="text-lg md:text-xl text-indigo-100 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ $data['subheading'] }}
            </p>
        @endif

        @if (! empty($data['button_label']) && ! empty($data['button_url']))
            <a
                href="{{ $data['button_url'] }}"
                class="inline-flex items-center justify-center bg-white text-indigo-700 px-8 py-3 rounded-lg font-semibold shadow-lg hover:bg-indigo-50 transition"
            >
                {{ $data['button_label'] }}
            </a>
        @endif
    </div>
</section>

{{--
    Block tipe "gallery": grid gambar responsif.
    Data: { images: [string, ...] } -- array path/URL gambar dari FileUpload.
--}}
<section class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-center mb-10">Galeri</h2>

    @if (! empty($data['images']))
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($data['images'] as $image)
                @php
                    // FileUpload multiple menyimpan array path/URL; dukung
                    // baik string murni maupun array { url, alt }.
                    $src = is_array($image) ? ($image['url'] ?? null) : $image;
                    $alt = is_array($image) ? ($image['alt'] ?? '') : '';
                @endphp

                @if (! empty($src))
                    <div class="aspect-w-4 aspect-h-3 rounded-lg overflow-hidden bg-gray-100">
                        <img
                            src="{{ $src }}"
                            alt="{{ $alt }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        >
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</section>

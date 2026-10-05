{{--
    Block tipe "contact": deskripsi + alamat email penerima.
    Data: { recipient_email, description }
--}}
<section class="max-w-3xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-center mb-6">Hubungi Kami</h2>

    @if (! empty($data['description']))
        <p class="text-gray-600 text-center mb-10 leading-relaxed">{{ $data['description'] }}</p>
    @endif

    @if (! empty($data['recipient_email']))
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <div class="flex items-center justify-center gap-3">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a href="mailto:{{ $data['recipient_email'] }}" class="text-lg font-medium text-indigo-600 hover:underline">
                    {{ $data['recipient_email'] }}
                </a>
            </div>
            <p class="mt-6 text-sm text-gray-500">
                Kirim pesan melalui <a href="{{ route('contact.index') }}" class="text-indigo-600 hover:underline">form kontak</a>.
            </p>
        </div>
    @endif
</section>

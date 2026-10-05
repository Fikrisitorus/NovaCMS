{{--
    Block tipe "faq": daftar pertanyaan dengan accordion (Alpine.js).
    Data: { questions: [{ question, answer }] }
--}}
<section class="max-w-3xl mx-auto px-4 py-16 sm:px-6 lg:px-8" x-data="{ open: null }">
    <h2 class="text-3xl font-bold text-center mb-10">Pertanyaan yang Sering Diajukan</h2>

    @if (! empty($data['questions']))
        <div class="space-y-3">
            @foreach ($data['questions'] as $index => $item)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button
                        type="button"
                        @click="open === {{ $index }} ? open = null : open = {{ $index }}"
                        class="w-full flex justify-between items-center px-5 py-4 text-left font-medium hover:bg-gray-50"
                        aria-expanded="false"
                    >
                        <span>{{ $item['question'] ?? '' }}</span>
                        <svg
                            class="h-5 w-5 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': open === {{ $index }} }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="open === {{ $index }}"
                        x-collapse
                        class="px-5 pb-4 text-gray-600"
                        style="display: none;"
                    >
                        {{ $item['answer'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

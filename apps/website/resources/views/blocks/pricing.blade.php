{{--
    Block tipe "pricing": kartu paket harga.
    Data: { plans: [{ name, price, features: [string, ...] }] }
--}}
<section class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Paket Harga</h2>

        @if (! empty($data['plans']))
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($data['plans'] as $plan)
                    <div class="bg-white rounded-xl border border-gray-200 p-8 relative {{
                        ! empty($plan['highlighted']) ? 'border-indigo-600 shadow-lg ring-2 ring-indigo-100' : ''
                    }}">
                        @if (! empty($plan['highlighted']))
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs px-3 py-1 rounded-full">
                                Populer
                            </span>
                        @endif

                        <h3 class="text-lg font-semibold mb-2">{{ $plan['name'] ?? '' }}</h3>

                        @if (! empty($plan['price']))
                            <div class="flex items-baseline gap-1 mb-6">
                                <span class="text-4xl font-bold">{{ $plan['price'] }}</span>
                                <span class="text-gray-500 text-sm">/bulan</span>
                            </div>
                        @endif

                        @if (! empty($plan['features']))
                            <ul class="space-y-3 mb-8">
                                @foreach ($plan['features'] as $feature)
                                    <li class="flex items-start gap-2 text-sm text-gray-600">
                                        <svg class="h-5 w-5 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{--
    View untuk Livewire component ContactForm (form kontak publik).
--}}
<div>
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="mb-6 flex items-start justify-between gap-3 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800"
        >
            <span>{{ session('success') }}</span>
            <button type="button" wire:ignore @click="show = false" class="text-green-600 hover:text-green-800" aria-label="Tutup">&times;</button>
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Nama</label>
            <input
                type="text"
                wire:model="name"
                id="name"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 @error('name') border-red-500 @enderror"
                placeholder="Nama Anda"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
            <input
                type="email"
                wire:model="email"
                id="email"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 @error('email') border-red-500 @enderror"
                placeholder="email@contoh.com"
            >
            @error('email')
                <p class="mt-1 text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="message">Pesan</label>
            <textarea
                wire:model="message"
                id="message"
                rows="5"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 @error('message') border-red-500 @enderror"
                placeholder="Tulis pesan..."
            ></textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition">
            Kirim Pesan
        </button>
    </form>
</div>

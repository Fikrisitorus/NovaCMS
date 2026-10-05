<nav class="bg-white border-b border-gray-200" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">
                    NovaCMS
                </a>
            </div>

            {{-- Menu desktop --}}
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-indigo-600">Beranda</a>
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600">Blog</a>
                <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-indigo-600">Kategori</a>
            </div>

            {{-- Tombol menu mobile --}}
            <div class="md:hidden flex items-center">
                <button
                    @click="open = !open"
                    class="text-gray-700 hover:text-indigo-600 focus:outline-none"
                    aria-label="Toggle menu"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="md:hidden border-t border-gray-200 bg-white"
        style="display: none;"
    >
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('home') }}" class="block py-2 text-gray-700 hover:text-indigo-600">Beranda</a>
            <a href="{{ route('blog.index') }}" class="block py-2 text-gray-700 hover:text-indigo-600">Blog</a>
            <a href="{{ route('categories.index') }}" class="block py-2 text-gray-700 hover:text-indigo-600">Kategori</a>
        </div>
    </div>
</nav>

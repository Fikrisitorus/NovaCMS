<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <p class="text-sm">&copy; {{ date('Y') }} NovaCMS. Dibuat dengan Laravel + Filament.</p>
            <div class="flex space-x-4 mt-4 md:mt-0">
                <a href="{{ route('blog.index') }}" class="text-sm hover:text-white">Blog</a>
                <a href="{{ route('categories.index') }}" class="text-sm hover:text-white">Kategori</a>
            </div>
        </div>
    </div>
</footer>

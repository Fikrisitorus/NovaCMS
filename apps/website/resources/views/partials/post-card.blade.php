<article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
    @if (! empty($post['featured_image']))
        <a href="{{ route('blog.show', $post['slug']) }}">
            <img
                src="{{ $post['featured_image'] }}"
                alt="{{ $post['title'] }}"
                class="w-full h-48 object-cover"
            >
        </a>
    @endif

    <div class="p-5">
        @if (! empty($post['categories']))
            <div class="flex gap-2 mb-2">
                @foreach ($post['categories'] as $category)
                    <a
                        href="{{ route('categories.show', $category['slug']) }}"
                        class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded hover:bg-indigo-100"
                    >
                        {{ $category['name'] }}
                    </a>
                @endforeach
            </div>
        @endif

        <h3 class="text-lg font-semibold mb-2">
            <a href="{{ route('blog.show', $post['slug']) }}" class="hover:text-indigo-600">
                {{ $post['title'] }}
            </a>
        </h3>

        @if (! empty($post['excerpt']))
            <p class="text-gray-600 text-sm line-clamp-3">{{ $post['excerpt'] }}</p>
        @endif

        <div class="mt-4 flex items-center gap-3 text-sm text-gray-500">
            @if (! empty($post['author']['name']))
                <span>{{ $post['author']['name'] }}</span>
                <span>&middot;</span>
            @endif
            @if (! empty($post['published_at']))
                <time>{{ \Illuminate\Support\Carbon::parse($post['published_at'])->translatedFormat('d M Y') }}</time>
            @endif
        </div>
    </div>
</article>

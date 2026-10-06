<?php

namespace App\Models;

use App\Models\Concerns\HtmlSanitizerCast;
use App\Models\Concerns\SanitizesHtml;
use App\Models\Traits\HasSeo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, HasSeo, HasUuids, SanitizesHtml;

    protected $fillable = [
        'website_id',
        'author_id',
        'title',
        'slug',
        'featured_image',
        'excerpt',
        'content',
        'is_published',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
            'content' => HtmlSanitizerCast::class,
        ];
    }

    /**
     * Boot method untuk auto-generate slug dari title.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Post $post) {
            if (filled($post->title) && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Scope: hanya post yang sudah terbit (is_published true dan
     * published_at sudah lewat). Dipakai oleh endpoint publik agar
     * rule filter tunggal — index, detail, maupun relasi kategori.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: cari post berdasarkan kata kunci pada title, excerpt,
     * dan content. Pencarian case-insensitive dengan LIKE (portabel
     * antara SQLite dev dan PostgreSQL produksi).
     */
    public function scopeSearch($query, ?string $q)
    {
        if (blank($q)) {
            return $query;
        }

        return $query->where(function ($query) use ($q) {
            $query->where('title', 'like', "%{$q}%")
                ->orWhere('excerpt', 'like', "%{$q}%")
                ->orWhere('content', 'like', "%{$q}%");
        });
    }

    /**
     * Get the website that owns the post.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the categories for the post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}

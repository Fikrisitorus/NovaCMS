<?php

namespace App\Models;

use App\Models\Contracts\Revisable;
use App\Models\Traits\HasRevisions;
use App\Models\Traits\HasSeo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model implements Revisable
{
    use HasFactory, HasRevisions, HasSeo, HasUuids;

    /**
     * Properti temporer untuk pesan commit revision (trait HasRevisions).
     * Tidak disimpan ke database — ditarik otomatis setelah event `saved`.
     */
    public ?string $revision_summary = null;

    protected $fillable = [
        'website_id',
        'title',
        'slug',
        'is_published',
        'blocks',
    ];

    /**
     * Konten halaman disimpan di kolom JSON `blocks`, jadi itulah
     * atribut yang di-snapshot ke version history.
     */
    public function revisionableAttribute(): string
    {
        return 'blocks';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
        ];
    }

    /**
     * Get the website that owns the page.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Scope: cari halaman berdasarkan kata kunci pada title dan slug.
     * Pencarian case-insensitive dengan LIKE (portabel SQLite/PostgreSQL).
     */
    public function scopeSearch($query, ?string $q)
    {
        if (blank($q)) {
            return $query;
        }

        return $query->where(function ($query) use ($q) {
            $query->where('title', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%");
        });
    }
}

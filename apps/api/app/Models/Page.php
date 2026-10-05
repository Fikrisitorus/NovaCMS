<?php

namespace App\Models;

use App\Models\Traits\HasSeo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory, HasSeo, HasUuids;

    protected $fillable = [
        'website_id',
        'title',
        'slug',
        'is_published',
        'blocks',
    ];

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
}

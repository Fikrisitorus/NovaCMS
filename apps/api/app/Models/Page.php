<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Page extends Model
{
    use HasUuids;

    protected $fillable = [
        'website_id',
        'title',
        'slug',
        'is_published',
        'blocks',
    ];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class SeoMeta extends Model
{
    use HasUuids;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'og_image',
        'canonical_url',
        'meta_keywords',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
        ];
    }

    /**
     * Get the parent seoable model (Page or Post).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}

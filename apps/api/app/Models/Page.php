<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Traits\HasSeo;


class Page extends Model
{
    use HasUuids, HasSeo;

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

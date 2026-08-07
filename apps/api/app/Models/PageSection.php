<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PageSection extends Model
{
    use HasUuids;

    protected $fillable = [
        'page_id',
        'type',
        'content',
        'order',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Get the page that owns the section.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}

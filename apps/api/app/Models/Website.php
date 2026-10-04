<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'domain',
        'is_active',
    ];

    /**
     * Get the pages for the website.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}

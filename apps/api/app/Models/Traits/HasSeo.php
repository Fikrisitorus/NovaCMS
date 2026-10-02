<?php

namespace App\Models\Traits;

use App\Models\SeoMeta;

use Illuminate\Database\Eloquent\Relations\MorphOne;


/**
 * Trait HasSeo
 *
 * Menambahkan relasi polymorphic ke SeoMeta.
 * Bisa digunakan oleh model Page, Post, atau model lainnya.
 */
trait HasSeo
{
    /**
     * Get the SEO metadata for this model.
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}

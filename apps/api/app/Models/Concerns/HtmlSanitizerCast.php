<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Render-side defence-in-depth for stored rich-text HTML.
 *
 * The primary protection is the SanitizesHtml trait, which purifies content on
 * save so the database only ever holds clean markup. This cast exists for the
 * case the trait cannot cover: a row written before the trait existed, or one
 * inserted by a script/seeder that bypasses model events. Reading the attribute
 * through this cast guarantees the value handed to a view is clean, regardless
 * of how it got into the database.
 *
 * Reading on every access is intentional: the cast only ever touches values
 * that are already HTML (Post::content / Page::blocks), and these models are
 * never loaded in bulk at scale in this app.
 */
class HtmlSanitizerCast implements CastsAttributes
{
    /**
     * Read the sanitized attribute.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! is_string($value)) {
            return $value;
        }

        $sanitized = clean($value);

        return $sanitized === '' ? null : $sanitized;
    }

    /**
     * Write the attribute through unchanged.
     *
     * Sanitizing on write is the job of the SanitizesHtml trait; duplicating it
     * here would only hide a trait that has stopped working.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}

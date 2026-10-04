<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Sanitizes HTML content on write so rich-text stored in the database is always
 * safe to render.
 *
 * Filament's RichEditor stores raw HTML produced in the browser. That HTML is
 * attacker-controlled: a user with access to the form can paste arbitrary
 * markup, so rendering it with {!! !!} would expose every visitor of the public
 * frontend to stored XSS.
 *
 * Cleaning on save (instead of only on render) makes the database the single
 * source of truth: any consumer - Blade views, HTML string attributes, JSON
 * payloads - can trust the stored value without having to remember to purify it
 * first. The render side is still defended in depth by the HtmlSanitizerCast,
 * in case a row was written before this trait existed or was inserted by a
 * script that bypasses model events.
 */
trait SanitizesHtml
{
    /**
     * Boot the trait.
     */
    protected static function bootSanitizesHtml(): void
    {
        static::saving(function (Model $model) {
            foreach (static::sanitizeHtmlAttributes() as $attribute) {
                if (! $model->isDirty($attribute)) {
                    continue;
                }

                $attributes = $model->getAttributes();
                $value = $attributes[$attribute] ?? null;

                // Non-strings (null, arrays, JSON-cast values) are left alone so
                // nullable columns keep returning null and JSON columns are not
                // mangled by the purifier.
                if (! is_string($value)) {
                    continue;
                }

                $model->setAttribute($attribute, clean($value));
            }
        });
    }

    /**
     * The attributes whose HTML should be purified before persistence.
     *
     * @return array<int, string>
     */
    protected static function sanitizeHtmlAttributes(): array
    {
        return ['content'];
    }
}

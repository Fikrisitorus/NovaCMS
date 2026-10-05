<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transformer untuk data Post (Blog).
 *
 * Menyertakan relasi author, categories, dan seo_meta hanya jika
 * di-eager-load oleh controller (whenLoaded) agar tidak terjadi
 * N+1 query saat endpoint dipanggil.
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])),
            'seo_meta' => $this->whenLoaded('seoMeta', fn () => [
                'meta_title' => $this->seoMeta->meta_title,
                'meta_description' => $this->seoMeta->meta_description,
                'og_image' => $this->seoMeta->og_image,
                'canonical_url' => $this->seoMeta->canonical_url,
                'meta_keywords' => $this->seoMeta->meta_keywords,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

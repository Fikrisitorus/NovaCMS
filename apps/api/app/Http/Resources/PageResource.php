<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Transformer untuk data Page.
 * Menyertakan data website induk dan konten halaman pada kolom 'blocks'.
 */
class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'website_id' => $this->website_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_published' => $this->is_published,
            'blocks' => $this->blocks,
            'website' => new WebsiteResource($this->whenLoaded('website')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

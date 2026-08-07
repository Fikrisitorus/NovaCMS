<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Transformer untuk data Page.
 * Menyertakan data website induk dan daftar sections jika di-load.
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
            'website' => new WebsiteResource($this->whenLoaded('website')),
            'sections' => PageSectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

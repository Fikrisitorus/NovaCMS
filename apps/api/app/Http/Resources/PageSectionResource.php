<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Transformer untuk data PageSection.
 * Field 'content' sudah di-cast sebagai array di model,
 * sehingga langsung dikembalikan sebagai objek JSON.
 */
class PageSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'type' => $this->type,
            'content' => $this->content,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

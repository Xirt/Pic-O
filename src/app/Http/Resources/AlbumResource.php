<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     	     => $this->id,
            'name'   	     => $this->name,
            'display_name'   => $this->display_name,
            'type'           => $this->type?->value,
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
            'date_precision' => $this->date_precision?->value,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'photos'         => $this->photos_count,
            'cover'          => $this->when($this->coverPhoto, fn () => new PhotoResource($this->coverPhoto)),
        ];
    }
}

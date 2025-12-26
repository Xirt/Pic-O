<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON resource for transforming a model into an API-friendly array.
 *
 * This resource handles formatting of model data for API responses,
 * including related entities, computed attributes, and conditional
 * fields. Use the `toArray()` method to define the structure returned
 * in JSON responses.
 */
class PhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
    *
    * @param Request $request
    *
    * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'path_full'     => route('photos.show', $this->id),
            'path_thumb'    => route('photos.thumbnail', $this->id),
            'path_download' => route('photos.download', $this->id),
            'taken_date'    => "Unknown",
            'taken_at'      => null,
            'taken_age'     => "Unknown",
        ];

        if ($this->taken_at)
        {
            $date = $this->taken_at;
            $data['taken_date'] = $date->format('j F Y');
            $data['taken_at']   = $date->format('j F Y, H:i:s');
            $data['taken_age']  = $date->isToday() ? 'Today' : $date->locale(app()->getLocale())->diffForHumans();
        }

        return array_merge(parent::toArray($request), $data);
    }
}

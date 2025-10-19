<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'path_full'     => route('photos.show', $this->id),
            'path_thumb'    => route('photos.thumbnail', $this->id),
            'path_download' => route('photos.download', $this->id),
            'taken_date'    => null,
            'taken_at'      => null,
            'taken_age'     => null,
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

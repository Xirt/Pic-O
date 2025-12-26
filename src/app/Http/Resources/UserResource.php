<?php

namespace App\Http\Resources;

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
class UserResource extends JsonResource
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
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role->value,
            'created_at' => $this->created_at,
        ];
    }
}

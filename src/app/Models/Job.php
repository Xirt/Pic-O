<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /**
     * Decode the payload and get the job class name.
     */
    public function getJobTypeAttribute(): string
    {
        $data = json_decode($this->payload, true);
        return $data['displayName'] ?? $data['job'] ?? 'Unknown';
    }

    /**
     * Scope to only include Pic-O jobs.
     */
    public function scopePicOJobs($query)
    {
        return $query->where(function ($q) {
            $q->where('payload', 'like', '%TraverseFolderJob%')
              ->orWhere('payload', 'like', '%ProcessPhotoJob%');
        });
    }
}

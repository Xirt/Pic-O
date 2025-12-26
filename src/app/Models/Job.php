<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a queued job in the application.
 *
 * Provides helper methods to decode the payload to a job type
 * and filter application-specific jobs (TraverseFolderJob, ProcessPhotoJob).
 */
class Job extends Model
{
    /**
     * Decode the payload and get the job class name.
     *
     * @return string The resolved job type or "Unknown" if unavailable
     */
    public function getJobTypeAttribute(): string
    {
        $data = json_decode($this->payload ?? '', true);
        return $data['displayName'] ?? $data['job'] ?? 'Unknown';
    }

    /**
     * Scope to only include Pic-O jobs.
     *
     * @param  Builder<Job> $query
     *
     * @return Builder<Job>
     */
    public function scopePicOJobs(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('payload', 'like', '%TraverseFolderJob%')
              ->orWhere('payload', 'like', '%ProcessPhotoJob%');
        });
    }
}

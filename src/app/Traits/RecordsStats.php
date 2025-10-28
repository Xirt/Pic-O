<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Enums\UserRole;

trait RecordsStats
{
    /**
     * Record a photo view (impression) for this model.
     */
    public function recordImpression(): void
    {
        $this->recordStat('impressions');
    }

    /**
     * Record a photo download for this model.
     */
    public function recordDownload(): void
    {
        $this->recordStat('downloads');
    }

    /**
     * Records an impression for the model if not viewed recently
     */
    public function recordStat(string $statColumn): void
    {
        $user = Auth::user();
        if ($user && $user->role === UserRole::ADMIN)
        {
            return;
        }

        if (!$this->hasColumn($statColumn))
        {
            return;
        }

        $cacheKey = $this->getCacheKey($statColumn);
        if (Cache::add($cacheKey, true, now()->addHour()))
        {
            $this->increment($statColumn);
        }
    }

    /**
     * Generate a unique cache key for this statistic, model and session
     */
    protected function getCacheKey(string $statColumn): string
    {
        $sessionId = session()->getId() ?? 'no-session';

        return sprintf('%s_%s_%s_%s',
            strtolower(class_basename($this)),
            $this->getKey(),
            $statColumn,
            $sessionId
        );
    }

    /**
     * Check if this model has the given column
     */
    protected function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->getAttributes());
    }
}

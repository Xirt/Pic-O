<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Enums\UserRole;

/**
 * Trait for recording usage statistics on a model, such as photo impressions and downloads.
 *
 * Provides methods to safely increment counters while avoiding duplicate counts
 * from the same session or admin users.
 */
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
     * Records a statistic for the model if it hasn't been recorded recently.
     *
     * @param string $statColumn Column name to increment (e.g., 'impressions', 'downloads')
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
     * Generate a unique cache key for this statistic, model, and session.
     *
     * @param string $statColumn Column name for which the cache key is generated
     *
     * @return string Unique cache key
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
     * Check if this model has the given column.
     *
     * @param string $column Column name to check
     *
     * @return bool True if the model has the column, false otherwise
     */
    protected function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->getAttributes());
    }
}

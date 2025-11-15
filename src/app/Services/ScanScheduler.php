<?php

namespace App\Services;

use App\Services\ScanService;

class ScanScheduler
{
    public function run()
    {
        $settings = [
            'interval'     => config('settings.scanner_interval', 'none'),
            'time'         => config('settings.scanner_time', '00:00'),
            'weekday'      => intval(config('settings.scanner_day_week', 0)),
            'day_of_month' => intval(config('settings.scanner_day_month', 1)),
        ];

        match ($settings['interval'])
        {
            'hourly'  => $this->runHourly(),
            'daily'   => $this->runDaily($settings['time']),
            'weekly'  => $this->runWeekly($settings['weekday'], $settings['time']),
            'monthly' => $this->runMonthly($settings['day_of_month'], $settings['time']),
            default   => null,
        };
    }

    /**
     * HOURLY runs immediately once per scheduler tick
     */
    private function runHourly(): void
    {
        app(ScanService::class)->run();
    }

    /**
     * DAILY at specific HH:MM
     */
    private function runDaily(string $time): void
    {
        if (now()->format('H:i') === $time)
        {
            app(ScanService::class)->run();
        }
    }

    /**
     * WEEKLY on specific weekday at HH:MM
     */
    private function runWeekly(int $weekday, string $time): void
    {
        $now = now();

        $isCorrectDay = $now->dayOfWeek == $weekday;
        if ($isCorrectDay && $now->format('H:i') === $time)
        {
            app(ScanService::class)->run();
        }
    }

    /**
     * MONTHLY:
     */
    private function runMonthly(int $dayOfMonth, string $time): void
    {
        $now = now();

        $runDay = min($dayOfMonth, $now->endOfMonth()->day);
        if ($now->day === $runDay && $now->format('H:i') === $time)
        {
            app(ScanService::class)->run();
        }
    }

}

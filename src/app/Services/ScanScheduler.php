<?php

namespace App\Services;

use App\Services\ScanService;

/**
 * Service responsible for scheduling automatic folder scans based on
 * configured intervals (hourly, daily, weekly, monthly).
 *
 * Determines when to trigger the ScanService according to application
 * settings and current date/time.
 */
class ScanScheduler
{
    /**
     * Run the scheduler according to configured interval.
     */
    public function run(): void
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
     * Run hourly: executes immediately once per scheduler tick.
     */
    private function runHourly(): void
    {
        app(ScanService::class)->run();
    }

    /**
     * Run daily at a specific HH:MM time.
     *
     * @param string $time Time in "HH:MM" format
     */
    private function runDaily(string $time): void
    {
        if (now()->format('H:i') === $time)
        {
            app(ScanService::class)->run();
        }
    }

   /**
     * Run weekly on a specific weekday at HH:MM.
     *
     * @param int    $weekday Day of week (0 = Sunday)
     * @param string $time    Time in "HH:MM" format
     */
    private function runWeekly(int $weekday, string $time): void
    {
        $now = now();

        $isCorrectDay = $now->dayOfWeek == ($weekday % 7);
        if ($isCorrectDay && $now->format('H:i') === $time)
        {
            app(ScanService::class)->run();
        }
    }

    /**
     * Run monthly on a specific day of the month at HH:MM.
     *
     * @param int    $dayOfMonth Day of the month (1-31)
     * @param string $time       Time in "HH:MM" format
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

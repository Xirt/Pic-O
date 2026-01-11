<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

/**
 * Handles enabling / disabling demo environment.
 */
class DemoModeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'demo:mode {state : on|off}';

    /**
     * The console command description.
     */
    protected $description = 'Enable or disable demo environment mode';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $state = strtolower($this->argument('state'));
        if (!in_array($state, ['on', 'off'], true))
        {
            $this->error('Invalid state. Use "on" or "off".');
            return Command::INVALID;
        }

        $value = $state === 'on' ? 1 : 0;
        Setting::updateOrCreate(
            ['key' => 'demo_environment'],
            ['value' => $value],
        );

        $this->info($value
            ? 'Demo environment ENABLED.'
            : 'Demo environment DISABLED.'
        );

        return Command::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;	

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		Setting::firstOrCreate(
			['key'   => 'site_title'],
			['value' => 'Pic-O']
		);

		Setting::firstOrCreate(
			['key'   => 'media_root'],
			['value' => '/photos']
		);
    }
}

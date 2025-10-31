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
			['key'   => 'site_name'],
			['value' => 'Pic-O']
		);

		Setting::firstOrCreate(
			['key'   => 'media_root'],
			['value' => '/photos']
		);

		Setting::firstOrCreate(
			['key'   => 'downscale_renders'],
			['value' => '0']
		);

		Setting::firstOrCreate(
			['key'   => 'cache_renders'],
			['value' => '0']
		);

		Setting::firstOrCreate(
			['key'   => 'session_persistent'],
			['value' => '0']
		);

		Setting::firstOrCreate(
			['key'   => 'album_name_tpl'],
			['value' => '{name} ({year})']
		);
    }
}

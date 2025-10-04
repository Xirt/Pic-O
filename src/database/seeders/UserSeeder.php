<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		// TODO :: Only create if there are no other admins
		User::firstOrCreate(
			['email' => 'admin@mydomain.com'],
			[
				'name'     => 'Admin',
				'password' => Hash::make('password'),
				'role'     => 'admin',
			]
		);
    }
}

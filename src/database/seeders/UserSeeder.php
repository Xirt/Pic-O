<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

use App\Enums\UserRole;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::where('role', UserRole::ADMIN->value)->doesntExist())
        {
    		User::firstOrCreate(
    			['email' => 'admin@mydomain.com'],
    			[
    				'name'     => 'Admin',
    				'password' => Hash::make('password'),
    				'role'     => UserRole::ADMIN->value,
    			]
    		);
        }
    }
}

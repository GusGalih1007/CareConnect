<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'role_name' => 'user',
            'description' => 'Default user with limited access'
        ]);
        Role::create([
            'role_name' => 'volunteer',
            'description' => 'Volunteer for delivering packages'
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Users;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleData = Role::create([
            'role_name' => 'Super Admin',
            'description' => 'Application main administrator'
        ]);

        Users::create([
            'username' => 'Admin Gusti',
            'email' => 'admingusti@gmail.com',
            'password' => bcrypt('123456789'),
            'phone' => '088978789090',
            'role_id' => $roleData->role_id,
            'user_type' => null,
            'avatar' => null,
            'bio' => null,
            'is_active' => true,
            'email_verified_at' => now()
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * I-create ang unang admin user sa ALI platform.
     */
    public function run(): void
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@ali.com',
            'password' => Hash::make('password123'),
            'status'   => 'active',
        ]);

        $admin->assignRole('admin');

        // Ipakita ang invite code para magamit sa register page
        $this->command->info('Admin created successfully!');
        $this->command->info('Email:        admin@ali.com');
        $this->command->info('Password:     password123');
        $this->command->info('Invite Code:  ' . $admin->invite_code);
        $this->command->info('Invite Link:  ' . $admin->inviteLink());
    }
}

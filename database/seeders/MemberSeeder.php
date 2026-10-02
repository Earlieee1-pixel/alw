<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * I-create ang test member ug leader accounts para sa development.
     */
    public function run(): void
    {
        // Pangitaa ang admin para ma-set as upline
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

        if (! $admin) {
            $this->command->error('Admin not found. Run AdminSeeder first.');
            return;
        }

        // Test leader account
        $leader = User::create([
            'name'        => 'Leader One',
            'email'       => 'leader@alw.com',
            'password'    => Hash::make('password123'),
            'referred_by' => $admin->id,
            'status'      => 'active',
        ]);
        $leader->assignRole('leader');

        // Test member account — downline sa leader
        $member = User::create([
            'name'        => 'Member One',
            'email'       => 'member@alw.com',
            'password'    => Hash::make('password123'),
            'referred_by' => $leader->id,
            'status'      => 'active',
        ]);
        $member->assignRole('member');

        $this->command->info('Test accounts created!');
        $this->command->info('');
        $this->command->info('Leader  → leader@alw.com  / password123');
        $this->command->info('Member  → member@alw.com  / password123');
        $this->command->info('');
        $this->command->info('Leader invite link: ' . $leader->inviteLink());
        $this->command->info('Member invite link: ' . $member->inviteLink());
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminReset extends Command
{
    protected $signature = 'admin:reset {--email=} {--password=} {--force : Create admin if missing}';
    protected $description = 'Force reset or create the single admin account.';

    public function handle(): int
    {
        $email = $this->option('email') ?: env('ADMIN_EMAIL', 'admin@example.com');
        $password = $this->option('password') ?: env('ADMIN_PASSWORD', 'admin12345');
        $forceCreate = $this->option('force');

        $admin = User::where('role','admin')->first();

        if (!$admin) {
            if (!$forceCreate) {
                $this->warn('No admin found. Use --force to create one.');
                return Command::FAILURE;
            }
            // Check email uniqueness
            if (User::where('email',$email)->exists()) {
                $this->error('Email already taken by another user. Specify a different --email.');
                return Command::FAILURE;
            }
            $admin = User::create([
                'full_name' => 'System Administrator',
                'name' => 'System Administrator',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'contact_number' => 'N/A',
                'boarding_house_name' => 'N/A',
                'role' => 'admin',
            ]);
            $this->info("Admin created: {$admin->email}");
            return Command::SUCCESS;
        }

        // Existing admin; update
        if ($email !== $admin->email) {
            if (User::where('email',$email)->where('id','!=',$admin->id)->exists()) {
                $this->error('Desired email already in use by another account. Choose a different one.');
                return Command::FAILURE;
            }
            $admin->email = $email;
        }
        $admin->password = Hash::make($password);
        if (!$admin->email_verified_at) {
            $admin->email_verified_at = now();
        }
        $admin->save();
        $this->info("Admin updated. Email: {$admin->email}");
        $this->line('You can now login using:');
        $this->line("Email: {$admin->email}");
        $this->line("Password: {$password}");
        return Command::SUCCESS;
    }
}

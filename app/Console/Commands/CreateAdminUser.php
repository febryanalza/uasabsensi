<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email?} {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for testing login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@company.com';
        $name = $this->argument('name') ?? 'Administrator';
        $password = 'password123';

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->info("User with email {$email} already exists!");
            return;
        }

        // Create admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'ADMIN',
            'karyawan_id' => null,
        ]);

        $this->info("Admin user created successfully!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->line("Role: ADMIN");
        
        return 0;
    }
}

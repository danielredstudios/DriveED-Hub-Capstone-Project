<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Artisan command to create or reset system admin accounts.
 * 
 * Usage:
 *   php artisan admin:create-system           # Interactive mode
 *   php artisan admin:create-system --reset   # Reset password for existing admin
 */
class CreateSystemAdmin extends Command
{
    protected $signature = 'admin:create-system 
                            {--reset : Reset password for an existing system admin}
                            {--email= : Email address for the system admin}
                            {--name= : Name for the system admin}
                            {--password= : Password for the system admin}';

    protected $description = 'Create or manage system administrator accounts';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔═══════════════════════════════════════════════════╗');
        $this->info('║       DriveED Hub - System Admin Management       ║');
        $this->info('╚═══════════════════════════════════════════════════╝');
        $this->info('');

        // Check current system admins
        $existingAdmins = Admin::where('role', 'system_admin')->get();
        
        if ($existingAdmins->count() > 0) {
            $this->info("Current System Admins ({$existingAdmins->count()}):");
            $this->table(
                ['ID', 'Name', 'Email', 'Created'],
                $existingAdmins->map(fn($a) => [
                    $a->id,
                    $a->name,
                    $a->email,
                    $a->created_at->format('M d, Y')
                ])->toArray()
            );
            $this->info('');
        }

        // Reset mode
        if ($this->option('reset')) {
            return $this->resetPassword($existingAdmins);
        }

        // Check limit
        if ($existingAdmins->count() >= 2) {
            $this->warn('⚠️  Maximum of 2 system admins already exist.');
            $this->info('Use --reset to reset a password instead.');
            return Command::SUCCESS;
        }

        // Create new system admin
        return $this->createAdmin();
    }

    protected function createAdmin(): int
    {
        $this->info('Creating new System Admin...');
        $this->info('');

        // Get name
        $name = $this->option('name') ?? $this->ask('Enter admin name');
        if (empty($name)) {
            $this->error('Name is required.');
            return Command::FAILURE;
        }

        // Get email
        $email = $this->option('email') ?? $this->ask('Enter admin email');
        
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:admins,email'
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email or email already exists.');
            return Command::FAILURE;
        }

        // Get password
        $password = $this->option('password') ?? $this->secret('Enter password (min 8 characters)');
        
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return Command::FAILURE;
        }

        // Confirm
        $this->info('');
        $this->info("Name: {$name}");
        $this->info("Email: {$email}");
        
        if (!$this->confirm('Create this system admin?', true)) {
            $this->info('Cancelled.');
            return Command::SUCCESS;
        }

        // Create
        $admin = Admin::create([
            'school_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'system_admin',
            'is_active' => true,
        ]);

        $this->info('');
        $this->info('✅ System Admin created successfully!');
        $this->info("Login at: /system-admin");
        $this->info("Email: {$email}");
        
        return Command::SUCCESS;
    }

    protected function resetPassword($existingAdmins): int
    {
        if ($existingAdmins->count() === 0) {
            $this->error('No system admins exist to reset.');
            return Command::FAILURE;
        }

        $email = $this->option('email');
        
        if (!$email) {
            $email = $this->choice(
                'Select admin to reset password',
                $existingAdmins->pluck('email')->toArray()
            );
        }

        $admin = Admin::where('email', $email)->where('role', 'system_admin')->first();
        
        if (!$admin) {
            $this->error('System admin not found with that email.');
            return Command::FAILURE;
        }

        $password = $this->option('password') ?? $this->secret('Enter new password (min 8 characters)');
        
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return Command::FAILURE;
        }

        $admin->update(['password' => Hash::make($password)]);

        $this->info('');
        $this->info("✅ Password reset for {$admin->name} ({$admin->email})");
        
        return Command::SUCCESS;
    }
}

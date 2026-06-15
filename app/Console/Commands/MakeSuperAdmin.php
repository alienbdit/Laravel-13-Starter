<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeSuperAdmin extends Command
{
    protected $signature = 'make:superadmin
                            {--email= : Email of an existing user to promote}
                            {--name= : Name for a new user}
                            {--password= : Password for a new user}';

    protected $description = 'Create a new superadmin user or promote an existing user to superadmin';

    public function handle(): int
    {
        $role = Role::where('name', 'super_admin')->first();

        if (! $role) {
            $this->error('super_admin role not found. Run: php artisan db:seed --class=RolePermissionSeeder');
            return self::FAILURE;
        }

        $email = $this->option('email') ?? $this->ask('Email address');

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->hasRole('super_admin')) {
                $this->info("User [{$existing->email}] already has the super_admin role.");
                return self::SUCCESS;
            }

            $existing->roles()->syncWithoutDetaching([$role->id]);
            $this->info("Promoted [{$existing->email}] to super_admin.");
            return self::SUCCESS;
        }

        // Create a new user
        $name = $this->option('name') ?? $this->ask('Name (used as username)');

        $password = $this->option('password') ?? $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name'     => ['required', 'string', 'max:255', 'unique:users,name'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $user->roles()->attach($role->id);

        $this->info("Superadmin [{$user->email}] created successfully.");
        return self::SUCCESS;
    }
}

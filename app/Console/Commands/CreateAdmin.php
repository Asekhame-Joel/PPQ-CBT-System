<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'exam:create-admin
        {--name= : Administrator name}
        {--email= : Administrator email address}
        {--phone= : Administrator phone number}';

    protected $description = 'Create the first active ExamForge administrator';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Administrator name'));
        $email = strtolower((string) ($this->option('email') ?: $this->ask('Administrator email address')));
        $phone = $this->option('phone');
        $password = (string) $this->secret('Password');
        $password_confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(compact('name', 'email', 'phone', 'password', 'password_confirmation'), [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->info("Administrator [{$email}] created successfully. Sign in at /admin.");

        return self::SUCCESS;
    }
}

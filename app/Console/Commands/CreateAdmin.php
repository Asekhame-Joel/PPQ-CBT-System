<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('exam:create-admin {--name=} {--email=} {--phone=}')]
#[Description('Create an active administrator account for the Exam Practice admin panel')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $input = Validator::make([
            'name' => trim((string) ($this->option('name') ?: $this->ask('Full name'))),
            'email' => Str::lower(trim((string) ($this->option('email') ?: $this->ask('Email address')))),
            'phone' => filled($phone = $this->option('phone'))
                ? trim((string) $phone)
                : null,
            'password' => $this->secret('Password'),
            'password_confirmation' => $this->secret('Confirm password'),
        ], [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        if ($input->fails()) {
            foreach ($input->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $data = $input->safe();

        $admin = User::create([
            'name' => $data->string('name')->toString(),
            'email' => $data->string('email')->toString(),
            'phone' => $data->string('phone')->toString() ?: null,
            'password' => $data->string('password')->toString(),
            'role' => UserRole::Admin,
            'department_id' => null,
            'level_id' => null,
            'is_active' => true,
        ]);

        $this->components->info("Administrator [{$admin->email}] created successfully.");

        return self::SUCCESS;
    }
}

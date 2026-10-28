<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin-user';

    protected $description = 'Create an administrator account for the CMS';

    public function handle(): int
    {
        $name = text('Administrator name', required: true);
        $email = text(
            'Administrator email',
            required: true,
            validate: fn (string $value): ?string => Validator::make(
                ['email' => $value],
                ['email' => ['required', 'email', 'max:255', 'unique:users,email']],
            )->errors()->first('email') ?: null,
        );

        $secret = password(
            'Password (minimum 12 characters)',
            required: true,
            validate: fn (string $value): ?string => Validator::make(
                ['password' => $value],
                ['password' => ['required', Password::min(12)]],
            )->errors()->first('password') ?: null,
        );
        $confirmation = password(
            'Confirm password',
            required: true,
            validate: fn (string $value): ?string => $value === $secret ? null : 'Passwords do not match.',
        );

        if ($secret !== $confirmation) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($secret),
        ]);
        $user->is_admin = true;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Administrator account created for {$user->email}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;

/**
 * Recovery for an administrator locked out of two-factor sign-in (lost phone, authenticator
 * entry from an earlier setup, recovery codes used up). Only someone with shell access to
 * the server can run it. The account must set up two-factor again on its next sign-in.
 */
class ResetTwoFactor extends Command
{
    protected $signature = 'app:reset-two-factor {email : Email address of the account} {--force : Skip the confirmation prompt}';

    protected $description = 'Turn off two-factor sign-in for an account so it can be set up again';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();

        if ($user === null) {
            $this->error("No account found for {$email}.");

            return self::FAILURE;
        }

        if (blank($user->getAppAuthenticationSecret())) {
            $this->info("Two-factor sign-in is not set up for {$user->email}. Nothing to reset.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Turn off two-factor sign-in for {$user->email}?", default: false)) {
            $this->warn('Cancelled. Nothing was changed.');

            return self::FAILURE;
        }

        $user->saveAppAuthenticationSecret(null);
        $user->saveAppAuthenticationRecoveryCodes(null);

        $this->info("Two-factor sign-in was turned off for {$user->email}.");
        if ($user->is_admin) {
            $this->line($user->hasEmailAuthentication()
                ? 'They can sign in with a code sent to their email, then set up the authenticator app again from their profile. Remove the old entry from the app first.'
                : 'They will be asked to set it up again after signing in with their password. Remove the old entry from the authenticator app first.');
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateReferralAccountsCommand extends Command
{
    protected $signature = 'referrals:generate-accounts {--dry-run : List users without creating accounts}';

    protected $description = 'Generate referral accounts for users who do not have one yet';

    public function handle(): int
    {
        $users = User::query()
            ->whereDoesntHave('referralAccount')
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->info('All users already have referral accounts.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Email'],
                $users->map(fn (User $user): array => [$user->id, $user->name, $user->email])->all(),
            );

            $this->info("{$users->count()} user(s) would receive referral accounts.");

            return self::SUCCESS;
        }

        $created = 0;

        foreach ($users as $user) {
            $user->createReferralAccount();
            $created++;
        }

        $this->info("Created {$created} referral account(s).");

        return self::SUCCESS;
    }
}

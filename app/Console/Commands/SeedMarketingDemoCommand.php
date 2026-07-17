<?php

namespace App\Console\Commands;

use Database\Seeders\MarketingDemoSeeder;
use Illuminate\Console\Command;

class SeedMarketingDemoCommand extends Command
{
    protected $signature = 'marketing:seed-demo {--overwrite : Overwrite existing marketing demo data if it already exists}';

    protected $description = 'Seed the Jasmina & Đorđe marketing demo account for screenshots and videos';

    public function handle(): int
    {
        $seeder = new MarketingDemoSeeder;
        $seeder->overwrite = (bool) $this->option('overwrite');
        $seeder->setCommand($this);
        $seeder->run();

        if ($seeder->skipped) {
            $this->warn('Marketing demo data already exists. Re-run with --overwrite to replace it.');

            return self::SUCCESS;
        }

        $this->info('Marketing demo data seeded successfully.');

        return self::SUCCESS;
    }
}

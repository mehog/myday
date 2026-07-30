<?php

namespace App\Console\Commands;

use Database\Seeders\MarketingDemoSeeder;
use Illuminate\Console\Command;

class SeedMarketingDemoCommand extends Command
{
    protected $signature = 'marketing:seed-demo
        {--overwrite : Overwrite existing marketing demo data if it already exists}
        {--locale=all : Seed one locale (bs|hr|de|en) or all}';

    protected $description = 'Seed localized marketing demo accounts for screenshots and videos';

    public function handle(): int
    {
        $localeOption = strtolower((string) $this->option('locale'));
        $locales = $localeOption === 'all'
            ? MarketingDemoSeeder::supportedLocales()
            : [$localeOption];

        foreach ($locales as $locale) {
            if (! in_array($locale, MarketingDemoSeeder::supportedLocales(), true)) {
                $this->error("Unsupported locale [{$locale}]. Use bs, hr, de, en, or all.");

                return self::FAILURE;
            }
        }

        $anySkipped = false;

        foreach ($locales as $locale) {
            $seeder = new MarketingDemoSeeder;
            $seeder->overwrite = (bool) $this->option('overwrite');
            $seeder->onlyLocale = $locale;
            $seeder->setCommand($this);
            $seeder->run();

            if ($seeder->skipped) {
                $anySkipped = true;
                $this->warn("[{$locale}] Marketing demo data already exists. Re-run with --overwrite to replace it.");

                continue;
            }

            foreach ($seeder->seededSummaries as $summary) {
                $this->info("[{$summary['locale']}] Seeded {$summary['email']} / {$summary['slug']}");
                $this->line('  Invitation: '.$summary['invitation_url']);
                $this->line('  Featured guest: '.$summary['featured_guest_url']);
            }
        }

        if (! $anySkipped) {
            $this->info('Marketing demo data seeded successfully.');
        }

        return self::SUCCESS;
    }
}

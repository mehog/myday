<?php

namespace Database\Seeders;

use App\Models\DiscountEmailTemplate;
use Illuminate\Database\Seeder;

class DiscountEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DiscountEmailTemplate::query()->updateOrCreate(
            ['name' => 'Activation discount'],
            [
                'is_active' => true,
                'subjects' => [
                    'en' => '{{discount_label}} off your NasDan invitation — code {{code}}',
                    'bs' => '{{discount_label}} popusta na NasDan pozivnicu — kod {{code}}',
                    'de' => '{{discount_label}} Rabatt auf Ihre NasDan-Einladung — Code {{code}}',
                    'hr' => '{{discount_label}} popusta na NasDan pozivnicu — kod {{code}}',
                ],
                'bodies' => [
                    'en' => "Your invitation for {{name}} is ready to activate.\nUse code {{code}} for {{discount_label}} off{{expires_clause}}.\nOpen pricing to choose a plan and enter the code at checkout.",
                    'bs' => "Vaša pozivnica za {{name}} spremna je za aktivaciju.\nIskoristite kod {{code}} za {{discount_label}} popusta{{expires_clause}}.\nOtvorite cijene, odaberite plan i unesite kod na naplati.",
                    'de' => "Ihre Einladung für {{name}} ist bereit zur Aktivierung.\nNutzen Sie den Code {{code}} für {{discount_label}} Rabatt{{expires_clause}}.\nÖffnen Sie die Preise, wählen Sie einen Plan und geben Sie den Code im Checkout ein.",
                    'hr' => "Vaša pozivnica za {{name}} spremna je za aktivaciju.\nIskoristite kod {{code}} za {{discount_label}} popusta{{expires_clause}}.\nOtvorite cijene, odaberite plan i unesite kod na naplati.",
                ],
            ],
        );

        DiscountEmailTemplate::query()->updateOrCreate(
            ['name' => 'Limited-time reminder'],
            [
                'is_active' => true,
                'subjects' => [
                    'en' => 'Reminder: use {{code}}{{expires_clause}}',
                    'bs' => 'Podsjetnik: iskoristite {{code}}{{expires_clause}}',
                    'de' => 'Erinnerung: {{code}}{{expires_clause}} nutzen',
                    'hr' => 'Podsjetnik: iskoristite {{code}}{{expires_clause}}',
                ],
                'bodies' => [
                    'en' => "Quick reminder for {{name}}: your {{discount_label}} discount code is {{code}}{{expires_clause}}.\nActivate your invitation while the offer is still available.",
                    'bs' => "Kratki podsjetnik za {{name}}: vaš kod za {{discount_label}} popusta je {{code}}{{expires_clause}}.\nAktivirajte pozivnicu dok je ponuda još dostupna.",
                    'de' => "Kurze Erinnerung für {{name}}: Ihr Rabattcode über {{discount_label}} ist {{code}}{{expires_clause}}.\nAktivieren Sie Ihre Einladung, solange das Angebot gilt.",
                    'hr' => "Kratki podsjetnik za {{name}}: vaš kod za {{discount_label}} popusta je {{code}}{{expires_clause}}.\nAktivirajte pozivnicu dok je ponuda još dostupna.",
                ],
            ],
        );
    }
}

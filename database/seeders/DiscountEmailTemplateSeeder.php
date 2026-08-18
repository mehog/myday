<?php

namespace Database\Seeders;

use App\Models\DiscountEmailTemplate;
use Illuminate\Database\Seeder;

class DiscountEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $brand = (string) config('app.name', 'NasDan');

        DiscountEmailTemplate::query()->updateOrCreate(
            ['name' => 'Activation discount'],
            [
                'is_active' => true,
                'subjects' => [
                    'en' => '{{discount_label}} off your '.$brand.' invitation — code {{code}}',
                    'bs' => '{{discount_label}} popusta na '.$brand.' pozivnicu — kod {{code}}',
                    'de' => '{{discount_label}} Rabatt auf Ihre '.$brand.'-Einladung — Code {{code}}',
                    'hr' => '{{discount_label}} popusta na '.$brand.' pozivnicu — kod {{code}}',
                    'sr_Latn' => '{{discount_label}} popusta na '.$brand.' pozivnicu — kod {{code}}',
                ],
                'bodies' => [
                    'en' => "Your invitation for {{name}} is ready to activate.\nUse code {{code}} for {{discount_label}} off{{expires_clause}}.\nOpen pricing to choose a plan and enter the code at checkout.",
                    'bs' => "Vaša pozivnica za {{name}} spremna je za aktivaciju.\nIskoristite kod {{code}} za {{discount_label}} popusta{{expires_clause}}.\nOtvorite cijene, odaberite plan i unesite kod na naplati.",
                    'de' => "Ihre Einladung für {{name}} ist bereit zur Aktivierung.\nNutzen Sie den Code {{code}} für {{discount_label}} Rabatt{{expires_clause}}.\nÖffnen Sie die Preise, wählen Sie einen Plan und geben Sie den Code im Checkout ein.",
                    'hr' => "Vaša pozivnica za {{name}} spremna je za aktivaciju.\nIskoristite kod {{code}} za {{discount_label}} popusta{{expires_clause}}.\nOtvorite cijene, odaberite plan i unesite kod na naplati.",
                    'sr_Latn' => "Vaša pozivnica za {{name}} spremna je za aktivaciju.\nIskoristite kod {{code}} za {{discount_label}} popusta{{expires_clause}}.\nOtvorite cene, izaberite plan i unesite kod na naplati.",
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
                    'sr_Latn' => 'Podsetnik: iskoristite {{code}}{{expires_clause}}',
                ],
                'bodies' => [
                    'en' => "Quick reminder for {{name}}: your {{discount_label}} discount code is {{code}}{{expires_clause}}.\nActivate your invitation while the offer is still available.",
                    'bs' => "Kratki podsjetnik za {{name}}: vaš kod za {{discount_label}} popusta je {{code}}{{expires_clause}}.\nAktivirajte pozivnicu dok je ponuda još dostupna.",
                    'de' => "Kurze Erinnerung für {{name}}: Ihr Rabattcode über {{discount_label}} ist {{code}}{{expires_clause}}.\nAktivieren Sie Ihre Einladung, solange das Angebot gilt.",
                    'hr' => "Kratki podsjetnik za {{name}}: vaš kod za {{discount_label}} popusta je {{code}}{{expires_clause}}.\nAktivirajte pozivnicu dok je ponuda još dostupna.",
                    'sr_Latn' => "Kratki podsetnik za {{name}}: vaš kod za {{discount_label}} popusta je {{code}}{{expires_clause}}.\nAktivirajte pozivnicu dok je ponuda još dostupna.",
                ],
            ],
        );

        DiscountEmailTemplate::query()->updateOrCreate(
            ['name' => 'Free plan is live'],
            [
                'is_active' => true,
                'subjects' => [
                    'en' => 'Your '.$brand.' invitation is live on Free',
                    'bs' => 'Vaša '.$brand.' pozivnica je aktivna na Besplatnom planu',
                    'de' => 'Ihre '.$brand.'-Einladung ist im Free-Plan live',
                    'hr' => 'Vaša '.$brand.' pozivnica je aktivna na Besplatnom planu',
                    'sr_Latn' => 'Vaša '.$brand.' pozivnica je aktivna na Besplatnom planu',
                ],
                'bodies' => [
                    'en' => "Hi {{name}}, your {$brand} invitation is now live on the Free plan — up to 50 guests, with no payment required to share it.\nOn Free, QR place card generation, sending push notifications, and seating PDF export stay locked.\nNeed more guests or those tools? Basic is 100 guests / 80, Plus 250 / 160, Premium unlimited / 240 (one-time).",
                    'bs' => "Zdravo {{name}}, vaša {$brand} pozivnica sada je aktivna na Besplatnom planu — do 50 gostiju, bez plaćanja da je podijelite.\nNa Besplatnom su zaključani: generisanje QR stolnih kartica, slanje push obavještenja i PDF rasporeda sjedenja.\nTreba vam više gostiju ili ti alati? Basic je 100 gostiju / 80, Plus 250 / 160, Premium neograničeno / 240 (jednokratno).",
                    'de' => "Hallo {{name}}, Ihre {$brand}-Einladung ist jetzt im Free-Plan live — bis zu 50 Gäste, ohne Zahlung zum Teilen.\nIm Free-Plan bleiben QR-Tischkarten-Generierung, Push-Versand und Sitzplan-PDF gesperrt.\nMehr Gäste oder diese Tools? Basic 100 Gäste / 80, Plus 250 / 160, Premium unbegrenzt / 240 (einmalig).",
                    'hr' => "Bok {{name}}, vaša {$brand} pozivnica sada je aktivna na Besplatnom planu — do 50 gostiju, bez plaćanja da je podijelite.\nNa Besplatnom su zaključani: generiranje QR stolnih kartica, slanje push obavijesti i PDF rasporeda sjedenja.\nTreba vam više gostiju ili ti alati? Basic je 100 gostiju / 80, Plus 250 / 160, Premium neograničeno / 240 (jednokratno).",
                    'sr_Latn' => "Zdravo {{name}}, vaša {$brand} pozivnica sada je aktivna na Besplatnom planu — do 50 gostiju, bez plaćanja da je podelite.\nNa Besplatnom su zaključani: generisanje QR stolnih kartica, slanje push obaveštenja i PDF rasporeda sedenja.\nTreba vam više gostiju ili ti alati? Basic je 100 gostiju / 80, Plus 250 / 160, Premium neograničeno / 240 (jednokratno).",
                ],
            ],
        );
    }
}

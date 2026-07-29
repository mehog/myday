<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultLocale = (string) config('app.default_locale', 'bs');

        Schema::table('wedding_events', function (Blueprint $table) use ($defaultLocale) {
            $table->string('invitation_locale', 5)->default($defaultLocale)->after('send_message');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('invitation_locale', 5)->nullable()->after('invite_platform');
        });

        $supported = config('app.supported_locales', ['en', 'bs', 'de']);

        foreach (DB::table('wedding_events')->select('id', 'user_id')->orderBy('id')->cursor() as $event) {
            $ownerLocale = null;

            if ($event->user_id !== null) {
                $ownerLocale = DB::table('users')->where('id', $event->user_id)->value('locale');
            }

            $locale = is_string($ownerLocale) && in_array($ownerLocale, $supported, true)
                ? $ownerLocale
                : $defaultLocale;

            DB::table('wedding_events')
                ->where('id', $event->id)
                ->update(['invitation_locale' => $locale]);
        }
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('invitation_locale');
        });

        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn('invitation_locale');
        });
    }
};

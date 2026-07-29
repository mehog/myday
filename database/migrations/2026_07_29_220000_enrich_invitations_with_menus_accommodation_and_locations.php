<?php

use App\PlatformMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_menu_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->cascadeOnDelete();
            $table->string('platform_key')->nullable();
            $table->string('label')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['wedding_event_id', 'platform_key']);
            $table->index(['wedding_event_id', 'sort_order']);
        });

        Schema::create('wedding_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_event_id', 'sort_order']);
        });

        Schema::table('wedding_events', function (Blueprint $table) {
            $table->boolean('accommodation_enabled')->default(false)->after('rsvp_deadline');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->foreignId('menu_option_id')
                ->nullable()
                ->after('rsvp_note')
                ->constrained('wedding_menu_options')
                ->nullOnDelete();
            $table->foreignId('plus_one_menu_option_id')
                ->nullable()
                ->after('menu_option_id')
                ->constrained('wedding_menu_options')
                ->nullOnDelete();
            $table->unsignedTinyInteger('accommodation_count')
                ->nullable()
                ->after('plus_one_menu_option_id');
        });

        Schema::table('guest_children', function (Blueprint $table) {
            $table->foreignId('menu_option_id')
                ->nullable()
                ->after('seating_name')
                ->constrained('wedding_menu_options')
                ->nullOnDelete();
        });

        $now = now();

        foreach (DB::table('wedding_events')->select('id')->orderBy('id')->cursor() as $event) {
            foreach (PlatformMenu::cases() as $platformMenu) {
                DB::table('wedding_menu_options')->insert([
                    'wedding_event_id' => $event->id,
                    'platform_key' => $platformMenu->value,
                    'label' => null,
                    'is_visible' => true,
                    'sort_order' => $platformMenu->defaultSortOrder(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $legacyLocations = DB::table('wedding_events')
            ->select(['id', 'location_name', 'location_address', 'location_lat', 'location_lng'])
            ->orderBy('id')
            ->get();

        foreach ($legacyLocations as $event) {
            $hasLocation = filled($event->location_name)
                || filled($event->location_address)
                || $event->location_lat !== null
                || $event->location_lng !== null;

            if (! $hasLocation) {
                continue;
            }

            DB::table('wedding_locations')->insert([
                'wedding_event_id' => $event->id,
                'label' => null,
                'name' => $event->location_name,
                'address' => $event->location_address,
                'lat' => $event->location_lat,
                'lng' => $event->location_lng,
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('guest_children', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_option_id');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plus_one_menu_option_id');
            $table->dropConstrainedForeignId('menu_option_id');
            $table->dropColumn('accommodation_count');
        });

        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn('accommodation_enabled');
        });

        Schema::dropIfExists('wedding_locations');
        Schema::dropIfExists('wedding_menu_options');
    }
};

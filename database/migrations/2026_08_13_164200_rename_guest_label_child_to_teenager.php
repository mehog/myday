<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->rewriteLabels('child', 'teenager');
    }

    public function down(): void
    {
        $this->rewriteLabels('teenager', 'child');
    }

    protected function rewriteLabels(string $from, string $to): void
    {
        DB::table('guests')
            ->whereNotNull('labels')
            ->orderBy('id')
            ->get(['id', 'labels'])
            ->each(function (object $guest) use ($from, $to): void {
                $labels = json_decode((string) $guest->labels, true);

                if (! is_array($labels)) {
                    return;
                }

                $updated = array_values(array_map(
                    fn (mixed $label): mixed => $label === $from ? $to : $label,
                    $labels,
                ));

                if ($updated === $labels) {
                    return;
                }

                DB::table('guests')
                    ->where('id', $guest->id)
                    ->update(['labels' => json_encode($updated)]);
            });
    }
};

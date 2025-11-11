<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'fingerprint')) {
                $table->string('fingerprint', 64)->nullable()->after('meta_json');
            }
            $table->index('fingerprint', 'submissions_fingerprint_idx');
        });

        DB::table('submissions')->orderBy('id')->chunkById(1000, function ($rows) {
            $normalizeName = fn(?string $s) => mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $s)));
            $normalizeEmail = fn(?string $s) => mb_strtolower(trim((string) $s));
            $normalizePhone = fn(?string $s) => preg_replace('/\D+/', '', (string) $s);
            $normalizeNotes = fn(?string $s) => preg_replace('/\s+/u', ' ', trim((string) $s));

            foreach ($rows as $r) {
                $fullNameRaw = $r->full_name ?: trim(trim((string) $r->first_name) . ' ' . trim((string) $r->last_name));

                $payload = [
                    'dealer_id' => (int)  ($r->dealer_id ?? 0),
                    'date' => (string) (optional($r->know_your_car_date)->format('Y-m-d') ?? $r->know_your_car_date ?? ''),
                    'full_name' => $normalizeName($fullNameRaw),
                    'email' => $normalizeEmail($r->email ?? ''),
                    'phone' => $normalizePhone($r->phone ?? ''),
                    'guest_count' => (int)  ($r->guest_count ?? 0),
                    'wants_appointment' => (int)  (!empty($r->wants_appointment)),
                    'notes' => $normalizeNotes($r->notes ?? ''),
                ];

                $fingerprint = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                if (empty($r->fingerprint)) {
                    DB::table('submissions')->where('id', $r->id)->update(['fingerprint' => $fingerprint]);
                }
            }
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                DELETE s2
                FROM submissions s1
                JOIN submissions s2
                  ON s1.fingerprint = s2.fingerprint
                 AND s1.id < s2.id
                WHERE s1.fingerprint IS NOT NULL
            ");
        } elseif ($driver === 'sqlite') {
            DB::statement("
                DELETE FROM submissions
                WHERE fingerprint IS NOT NULL
                  AND id NOT IN (
                    SELECT MIN(id)
                    FROM submissions
                    WHERE fingerprint IS NOT NULL
                    GROUP BY fingerprint
                  )
            ");
        } else {
            DB::statement("
                DELETE FROM submissions s
                USING submissions d
                WHERE s.fingerprint = d.fingerprint
                  AND s.id > d.id
                  AND s.fingerprint IS NOT NULL
            ");
        }

        Schema::table('submissions', function (Blueprint $table) {
            try {
                $table->unique('fingerprint', 'submissions_fingerprint_unique');
            } catch (\Throwable $e) {
                //
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            try {
                $table->dropUnique('submissions_fingerprint_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('submissions_fingerprint_idx');
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('submissions', 'fingerprint')) {
                $table->dropColumn('fingerprint');
            }
        });
    }
};

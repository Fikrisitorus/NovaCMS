<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan unique index pada kolom slug di posts dan pages.
     *
     * Sebelumnya slug tidak punya constraint unique di database, sehingga
     * auto-generate Str::slug() bisa membentuk duplikat dan menyebabkan
     * ambigu saat lookup by slug. Unique rule di Filament form hanya bekerja
     * saat input lewat form; race condition atau penulisan langsung tetap
     * bisa lolos, jadi constraint di level database tetap diperlukan.
     *
     * Index dibuat dengan CREATE INDEX mentah (bukan ->change()) supaya
     * portabel antara SQLite/MySQL/PostgreSQL dan tidak butuh doctrine/dbal.
     */
    public function up(): void
    {
        foreach (['posts', 'pages'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'slug')) {
                continue;
            }

            $this->deduplicateSlugs($table);

            DB::statement("CREATE UNIQUE INDEX {$table}_slug_unique ON {$table} (slug)");
        }
    }

    public function down(): void
    {
        foreach (['posts', 'pages'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // SQLite dan PostgreSQL pakai nama index standalone; MySQL juga
            // menerima DROP INDEX dengan nama index-nya langsung.
            try {
                DB::statement("DROP INDEX {$table}_slug_unique");
            } catch (Throwable $e) {
                // Index tidak ada — tidak ada yang perlu dilakukan.
            }
        }
    }

    /**
     * Rename slug duplikat menjadi slug-2, slug-3, ... agar unique index
     * bisa dipasang tanpa error constraint violation. Baris pertama (id
     * terkecil) tetap memakai slug aslinya.
     */
    protected function deduplicateSlugs(string $table): void
    {
        $idColumn = Schema::hasColumn($table, 'id') ? 'id' : null;

        if ($idColumn === null) {
            return;
        }

        $duplicates = DB::table($table)
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        foreach ($duplicates as $slug) {
            $ids = DB::table($table)
                ->where('slug', $slug)
                ->orderBy($idColumn)
                ->pluck($idColumn);

            // Lewati record pertama; sisanya diberi suffix -2, -3, ...
            foreach ($ids->slice(1)->values() as $index => $id) {
                $suffix = $index + 2;
                $newSlug = $slug.'-'.$suffix;

                while (DB::table($table)->where('slug', $newSlug)->exists()) {
                    $suffix++;
                    $newSlug = $slug.'-'.$suffix;
                }

                DB::table($table)
                    ->where($idColumn, $id)
                    ->update(['slug' => $newSlug]);
            }
        }
    }
};

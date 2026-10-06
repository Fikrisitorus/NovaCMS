<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan website_id ke tabel media untuk isolasi multi-tenant.
 *
 * Media sebelumnya bersifat global (dibagi semua website). Kolom ini
 * membuat setiap media milik satu website sehingga endpoint publik
 * hanya mengembalikan media milik pemilik kunci API yang dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // nullable agar media lama tidak rusak saat migrasi; endpoint
            // publik memfilter whereNotNull, danSeeder/admin mengisi saat
            // upload.
            $table->foreignUuid('website_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->index('website_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['website_id']);
            $table->dropConstrainedForeignId('website_id');
        });
    }
};

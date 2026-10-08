<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel polymorphic `content_revisions` menyimpan snapshot konten
     * (kolom `blocks` pada Page / kolom `content` pada Post) setiap kali
     * record diperbarui, sehingga admin bisa melihat riwayat perubahan dan
     * memulihkan versi lama dari Filament panel.
     */
    public function up(): void
    {
        Schema::create('content_revisions', function (Blueprint $table) {
            // UUID primary key, mengikuti konvensi seluruh model NovaCMS.
            $table->uuid('id')->primary();

            // Relasi polymorphic ke model induk (Page / Post).
            $table->string('revisable_type');
            $table->uuid('revisable_id');

            // Snapshot konten selalu berbentuk JSON object:
            // {"attribute": "blocks"|"content", "data": <nilai konten>}.
            // Dibungkus object agar satu kolom json bisa menyimpan tipe nilai
            // apa pun (array blocks maupun string HTML content) sekaligus
            // mencatat nama atribut yang dipulihkan saat tombol Restore dipakai.
            $table->json('content');

            // User yang melakukan perubahan. Nullable karena update bisa
            // terjadi di luar sesi autentikasi (seeder, artisan tinker, job).
            $table->uuid('user_id')->nullable();

            // Pesan commit opsional untuk perubahan (mirip commit message).
            $table->string('summary')->nullable();

            // Revision bersifat audit log: hanya ada created_at, tidak ada
            // updated_at (record tidak pernah diubah, hanya dibuat/dibaca).
            $table->timestamp('created_at');

            $table->index(['revisable_type', 'revisable_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};

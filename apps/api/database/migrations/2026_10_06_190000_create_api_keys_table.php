<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel api_keys: menyimpan kunci API yang dipakai frontend untuk
 * mengakses endpoint publik. Sebuah website bisa memiliki banyak kunci
 * (mis. satu untuk production, satu untuk staging) yang masing-masing
 * bisa ditarik (revoke) tanpa mengganggu yang lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            // Format: novacms_<random> — prefix mempermudah identifikasi
            // kunci yang tidak sengaja ter-commit ke repo publik.
            $table->uuid('id')->primary();
            $table->foreignUuid('website_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key')->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};

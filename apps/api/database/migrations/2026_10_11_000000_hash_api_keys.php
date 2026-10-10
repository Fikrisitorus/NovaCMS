<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah cara penyimpanan kunci API dari plaintext menjadi hash.
 *
 * Sebelumnya kolom `api_keys.key` berisi plaintext kunci — siapa pun
 * yang bisa membaca DB langsung mendapatkan akses API. Migration ini:
 *  1. Menambah kolom `key_prefix` (12 karakter pertama plaintext) yang
 *     dipakai untuk mempersempit lookup saat validasi request.
 *     12 dipakai (bukan 8) karena 8 karakter pertama selalu 'novacms_'
 *     — prefix 8 tidak mengecilkan kandidat sama sekali.
 *  2. Mengisi `key_prefix` dari plaintext yang ada, lalu mengganti
 *     isi `key` dengan Hash::make(plaintext).
 *
 * Setelah migration ini berjalan, plaintext lama tidak bisa dipulihkan
 * lagi — itu memang tujuannya. Kolom `key` tetap unik karena hash
 * bcrypt bernilai unik per plaintext.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            // Prefix dipakai untuk lookup + identifikasi yang aman
            // ditampilkan di UI. Dibuat dulu sebagai nullable karena
            // baris lama belum memilikinya sampai data diisi di bawah.
            $table->string('key_prefix', 12)->nullable()->after('key');
        });

        // Index eksplisit (bukan $table->index()) karena SQLite
        // memerlukan nama index yang diturunkan dari nama tabel +
        // kolom agar bisa di-drop di down().
        DB::statement('CREATE INDEX api_keys_key_prefix_index ON api_keys (key_prefix)');

        // Hashing harus dilakukan per-baris di PHP (Hash::make) —
        // tidak ada ekspresi SQL portabel untuk bcrypt. Ambil baris
        // secara bertahap agar tabel besar tidak dimuat sekaligus.
        DB::table('api_keys')->orderBy('id')->chunk(100, function ($keys): void {
            foreach ($keys as $key) {
                // plaintext lama masih ada di kolom key sebelum di-hash.
                $plain = $key->key;

                DB::table('api_keys')
                    ->where('id', $key->id)
                    ->update([
                        // Bila data sudah berupa hash (mis. migration
                        // pernah jalan sebagian), jangan hash ulang.
                        'key_prefix' => substr($plain, 0, 12),
                        'key' => str_starts_with($plain, '$2y$')
                            ? $plain
                            : Hash::make($plain),
                    ]);
            }
        });

        // Setelah seluruh baris punya prefix, jadikan tidak null agar
        // lookup prefix selalu mendapat kandidat yang lengkap.
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->string('key_prefix', 12)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        // Plaintext tidak bisa dipulihkan dari hash bcrypt — kolom `key`
        // tetap berisi hash setelah rollback. Hanya prefix-nya yang
        // dibuang karena tidak dipakai lagi oleh lookup versi lama.

        // SQLite butuh index di-drop sebelum kolomnya di-drop, dan
        // dropColumn harus berdiri sendiri tanpa modifier lain.
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropIndex('api_keys_key_prefix_index');
        });

        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn('key_prefix');
        });
    }
};

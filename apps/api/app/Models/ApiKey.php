<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * ApiKey — kunci yang dipakai frontend untuk mengakses API publik.
 *
 * Kolom `key` hanya menyimpan **hash** dari plaintext kunci (bcrypt),
 * sama seperti password user — plaintext tidak pernah disimpan setelah
 * kunci dibuat. Untuk tetap bisa mencari kunci saat validasi request
 * tanpa memeriksa seluruh tabel, kolom `key_prefix` menyimpan 12
 * karakter pertama plaintext (bukan 8) — 8 karakter pertama adalah
 * prefix tetap 'novacms_', jadi 8 saja tidak mempersempit kandidat
 * sama sekali. 12 karakter masih menyisakan ruang identifikasi aman
 * untuk ditampilkan di UI.
 *
 * Sebuah website bisa memiliki banyak kunci; setiap kunci bisa
 * di-revoke (soft) dengan mengisi revoked_at tanpa menghapus barisnya,
 * sehingga audit trail tetap utuh.
 */
class ApiKey extends Model
{
    use HasFactory, HasUuids;

    /**
     * Plaintext sementara — BUKAN kolom DB, hanya property biasa.
     *
     * Diisi otomatis oleh mutator `key` saat plaintext pertama kali
     * di-set (create) sehingga pemanggil (notifikasi Filament, output
     * CLI, test) masih bisa menampilkan plaintext sekali sebelum
     * dilupakan. Karena property biasa, nilainya tidak pernah ikut
     * ke query INSERT maupun toArray/toJson.
     */
    public ?string $plain_text_key = null;

    /**
     * Cache hash per-plaintext dalam satu instance model (lihat
     * penjelasan di setKeyAttribute). Tidak ikut ke query.
     *
     * @var array<string, string>
     */
    protected array $keyHashCache = [];

    protected $fillable = [
        'website_id',
        'name',
        'key',
        'key_prefix',
        'last_used_at',
        'revoked_at',
    ];

    /**
     * Sembunyikan hash dari toArray/toJson — hash tidak berguna untuk
     * klien dan bocorinya hanya menambah permukaan serangan.
     */
    protected $hidden = [
        'key',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Saat `key` diisi dengan plaintext, simpan hash-nya di kolom `key`
     * dan 12 karakter pertama plaintext di `key_prefix`. Plaintext asli
     * tetap bisa diakses lewat property ->plain_text_key untuk
     * ditampilkan sekali ke user (notifikasi Filament / output CLI).
     *
     * Hash di-cache per-plaintext: Hash::make menghasilkan salt acak
     * tiap pemanggilan, sehingga hash untuk plaintext yang sama selalu
     * berbeda. Eloquent membandingkan attributes dengan original untuk
     * menentukan kolom yang dirty — tanpa caching, pengisian `key`
     * kedua kali (mis. oleh afterMaking factory) menghasilkan hash baru
     * dan kolom `key` dianggap tidak berubah, lalu tidak ikut tertulis
     * saat insert.
     */
    public function setKeyAttribute(?string $plaintext): void
    {
        if (blank($plaintext)) {
            $this->attributes['key'] = null;
            $this->keyHashCache = [];

            return;
        }

        // Simpan plaintext ke property biasa (bukan attributes) agar
        // tidak ikut tertulis ke database.
        $this->plain_text_key = $plaintext;
        $this->attributes['key_prefix'] = substr($plaintext, 0, 12);
        $this->attributes['key'] = $this->keyHashCache[$plaintext] ??= Hash::make($plaintext);
    }

    /**
     * Konfirmasi plaintext cocok dengan hash yang tersimpan.
     */
    public function checkPlainText(string $plaintext): bool
    {
        return Hash::check($plaintext, $this->key);
    }

    /**
     * Apakah kunci masih aktif (belum di-revoke)?
     */
    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * Ambil kunci aktif berdasarkan plaintext key. Mengembalikan null
     * bila tidak ditemukan atau sudah di-revoke.
     *
     * Karena `key` hanya menyimpan hash, pencarian tidak bisa dilakukan
     * dengan WHERE langsung. Prefix 8 karakter pertama plaintext
     * mempersempit kandidat (prefix berfungsi juga sebagai identifikasi
     * aman yang ditampilkan di UI), lalu setiap kandidat diuji dengan
     * Hash::check untuk memastikan kecocokan penuh.
     */
    public static function findActive(string $plaintext): ?ApiKey
    {
        if (strlen($plaintext) < 1) {
            return null;
        }

        $candidates = static::query()
            ->where('key_prefix', substr($plaintext, 0, 12))
            ->whereNull('revoked_at')
            ->get();

        /** @var ApiKey $candidate */
        foreach ($candidates as $candidate) {
            if ($candidate->checkPlainText($plaintext)) {
                return $candidate;
            }
        }

        return null;
    }
}

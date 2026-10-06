<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ApiKey — kunci yang dipakai frontend untuk mengakses API publik.
 *
 * Sebuah website bisa memiliki banyak kunci; setiap kunci bisa
 * di-revoke (soft) dengan mengisi revoked_at tanpa menghapus barisnya,
 * sehingga audit trail tetap utuh.
 */
class ApiKey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'website_id',
        'name',
        'key',
        'last_used_at',
        'revoked_at',
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
     * Apakah kunci masih aktif (belum di-revoke)?
     */
    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * Ambil kunci aktif berdasarkan plaintext key. Mengembalikan null
     * bila tidak ditemukan atau sudah di-revoke.
     */
    public static function findActive(string $key): ?ApiKey
    {
        return static::where('key', $key)
            ->whereNull('revoked_at')
            ->first();
    }
}

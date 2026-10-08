<?php

namespace App\Models;

use App\Models\Contracts\Revisable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Snapshot konten sebuah record Revisable (Page / Post) pada satu titik waktu.
 *
 * Record dibuat otomatis oleh trait HasRevisions setiap kali induk di-update.
 * Kolom `content` memuat object JSON:
 *   {"attribute": "blocks"|"content", "data": <nilai konten>}
 * sehingga Restore tahu atribut mana yang harus ditulis ulang.
 */
class Revision extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'content_revisions';

    protected $fillable = [
        'revisable_type',
        'revisable_id',
        'content',
        'user_id',
        'summary',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Model induk yang di-snapshot (Page atau Post).
     */
    public function revisable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User yang melakukan perubahan yang memicu snapshot ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nama atribut induk yang disimpan pada snapshot ('blocks' / 'content').
     */
    public function revisionedAttribute(): string
    {
        return (string) ($this->content['attribute'] ?? 'content');
    }

    /**
     * Nilai konten murni dari snapshot (array blocks atau string HTML).
     */
    public function revisionedData(): mixed
    {
        return $this->content['data'] ?? null;
    }

    /**
     * Snapshot JSON pretty-print untuk ditampilkan di Filament.
     */
    public function formattedContent(): string
    {
        return json_encode(
            is_array($this->content) ? $this->content : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Label singkat untuk tipe induk revision ('Page' / 'Post').
     */
    public function revisableLabel(): string
    {
        return class_basename($this->revisable_type);
    }

    /**
     * Pulihkan konten dari snapshot ini ke model induknya.
     *
     * @param  string|null  $summary  Pesan commit untuk revision baru.
     * @return bool True jika induk ditemukan dan berhasil dipulihkan.
     */
    public function restore(?string $summary = null): bool
    {
        $revisable = $this->revisable;

        if (! $revisable instanceof Revisable || ! $revisable instanceof Model) {
            return false;
        }

        $attribute = $this->revisionedAttribute();

        if (! $revisable->isFillable($attribute)) {
            return false;
        }

        // Update lewat save() agar model event (termasuk pembuatan revision
        // otomatis) tetap jalan, bukan update() yang membypass instance ini.
        $revisable->{$attribute} = $this->revisionedData();
        $revisable->revision_summary = $summary;
        $revisable->save();

        return true;
    }

    /**
     * Scope: revision terbaru lebih dulu.
     *
     * Di-sort sekunder berdasarkan `id` karena seluruh model NovaCMS memakai
     * UUID v7 (terurut berdasarkan waktu). Presisi `created_at` pada SQLite
     * hanya sampai detik, sehingga beberapa revision yang dibuat dalam detik
     * yang sama bisa tie kalau hanya di-sort berdasarkan timestamp.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Scope: revision terlama lebih dulu (riwayat kronologis).
     */
    public function scopeOldestFirst($query)
    {
        return $query->orderBy('created_at')->orderBy('id');
    }
}

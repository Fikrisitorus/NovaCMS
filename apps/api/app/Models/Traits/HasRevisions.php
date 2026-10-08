<?php

namespace App\Models\Traits;

use App\Models\Contracts\Revisable;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasRevisions
 *
 * Menyediakan version history polymorphic untuk model dengan konten:
 * Page (kolom `blocks`) dan Post (kolom `content`).
 *
 * Mekanisme:
 * 1. `updating` — snapshot konten SEBELUM update disimpan ke
 *    `content_revisions`, sehingga riwayat selalu merekam keadaan lama.
 * 2. `saved` — setelah induk ditulis, properti temporer `revision_summary`
 *    direset (nilainya sudah dikonsumsi di langkah 1).
 *
 * Pesan commit di-pass via properti `revision_summary` pada instance model:
 *     $post->revision_summary = 'Perbaiki typo';
 *     $post->update(['content' => '...']);
 * Properti itu tidak masuk database — dideklarasikan secara eksplisit di
 * tiap model yang memakai trait ini agar tidak ikut terbawa ke query.
 */
trait HasRevisions
{
    /**
     * Boot trait: daftarkan listener membuat revision otomatis.
     */
    protected static function bootHasRevisions(): void
    {
        // Snapshot konten LAMA sebelum perubahan benar-benar ditulis,
        // agar history mencerminkan keadaan sebelum diperbarui.
        static::updating(function (Model $model) {
            if (! $model->isRevisableContentDirty()) {
                return;
            }

            $model->createRevision($model->getRevisionSummary());
        });

        // Reset properti temporer setelah dipakai, baik saat update
        // berhasil maupun saat kontennya tidak berubah.
        static::saved(function (Model $model) {
            $model->revision_summary = null;
        });
    }

    /**
     * Nama atribut konten yang akan di-snapshot.
     */
    public function revisionableAttribute(): string
    {
        return 'content';
    }

    /**
     * Apakah atribut konten sedang berubah pada operasi ini.
     */
    public function isRevisableContentDirty(): bool
    {
        return $this->isDirty($this->revisionableAttribute());
    }

    /**
     * Relasi ke seluruh revision record ini.
     */
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisable')->latestFirst();
    }

    /**
     * Revision terbaru (paling akhir dibuat), atau null bila belum ada.
     */
    public function latestRevision()
    {
        return $this->revisions()->first();
    }

    /**
     * Revision terlama (snapshot pertama yang pernah dibuat), atau null.
     */
    public function oldestRevision()
    {
        return $this->revisions()->oldestFirst()->first();
    }

    /**
     * Buat snapshot konten saat ini ke tabel revisions.
     *
     * @param  string|null  $summary  Pesan commit opsional.
     * @return Revision|null Revision baru, atau null bila tidak ada konten yang berubah.
     */
    public function createRevision(?string $summary = null): ?Revision
    {
        $attribute = $this->revisionableAttribute();

        /** @var Revisable&Model $this */
        return Revision::create([
            'revisable_type' => static::class,
            'revisable_id' => $this->getKey(),
            'content' => [
                'attribute' => $attribute,
                'data' => $this->getOriginal($attribute),
            ],
            'user_id' => Auth::id(),
            'summary' => $summary,
            'created_at' => now(),
        ]);
    }

    /**
     * Ambil pesan commit yang sedang melekat pada instance ini, atau null.
     *
     * Implementasi di model (Page/Post) wajib menyediakan properti
     * `?string $revision_summary` (deklarasi eksplisit mencegah Eloquent
     * memperlakukannya sebagai atribut database).
     */
    public function getRevisionSummary(): ?string
    {
        return $this->revision_summary ?? null;
    }
}

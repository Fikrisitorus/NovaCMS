<?php

namespace App\Models\Contracts;

use App\Models\Revision;

/**
 * Contract untuk model yang kontennya di-snapshot ke tabel
 * `content_revisions` (saat ini Page dan Post).
 *
 * Implementasi diberikan oleh trait App\Models\Traits\HasRevisions.
 * Pemanggil yang memakai contract ini bertanggung jawab memastikan
 * objeknya juga adalah Illuminate\Database\Eloquent\Model.
 */
interface Revisable
{
    /**
     * Nama atribut yang menjadi sumber snapshot konten.
     *
     * Page -> 'blocks' (JSON), Post -> 'content' (HTML string).
     */
    public function revisionableAttribute(): string;

    /**
     * Simpan snapshot konten saat ini ke tabel revisions.
     *
     * @param  string|null  $summary  Pesan commit opsional.
     * @return Revision|null Revision baru, atau null bila tidak ada konten yang berubah.
     */
    public function createRevision(?string $summary = null): ?Revision;
}

<?php

namespace App\Policies;

use App\Models\Revision;
use App\Models\User;

/**
 * Policy untuk model Revision.
 *
 * Revision bersifat audit log read-only: tidak ada operasi create/edit/delete
 * manual melalui panel. Yang diizinkan hanya melihat daftar/membaca snapshot
 * dan memulihkan (restore) konten ke model induk.
 *
 * Karena tidak ada entri ResourcePermissions untuk Revision di filament-shield,
 * gunakan permission generic 'view_any_content' dan 'restore_content' yang
 * melekat pada role admin (super_admin memakai Gate::before di panel Filament).
 */
class RevisionPolicy
{
    /**
     * Daftar revision hanya boleh dilihat user yang punya akses konten.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_content');
    }

    public function view(User $user, Revision $revision): bool
    {
        return $user->can('view_content');
    }

    /**
     * Restore dianggap operasi write pada konten induk, jadi diproteksi
     * dengan permission update konten.
     */
    public function restore(User $user, Revision $revision): bool
    {
        return $user->can('restore_content');
    }
}

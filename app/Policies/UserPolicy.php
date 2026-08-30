<?php

namespace App\Policies;

use App\Models\User;

/**
 * Hanya administrator yang boleh mengurus akun pengurus lain.
 *
 * Tidak memakai PengurusPolicy: di sana semua peran boleh *melihat* daftar,
 * dan daftar akun bukan sesuatu yang perlu dibaca bendahara atau sekretaris.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administrator tidak boleh menghapus dirinya sendiri: itu jalur tercepat
     * menuju panel tanpa satu pun administrator yang tersisa.
     */
    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin() && ! $user->is($target);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

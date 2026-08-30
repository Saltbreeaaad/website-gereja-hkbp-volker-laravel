<?php

namespace App\Policies;

use App\Models\User;

/**
 * Dasar perizinan untuk seluruh sumber daya panel admin.
 *
 * Pola izinnya sama di semua modul, hanya daftar perannya yang berbeda, jadi
 * aturannya ditulis sekali di sini dan tiap modul cukup menyebut siapa yang
 * boleh menulis.
 *
 * Tiga lapis:
 *
 * 1. **Melihat** — semua pengurus. Bendahara tetap perlu membaca jadwal
 *    ibadah, sekretaris tetap perlu membaca kas. Menyembunyikan menu dari
 *    peran yang tidak boleh mengubahnya hanya membuat orang saling bertanya
 *    lewat WhatsApp.
 * 2. **Menambah & menyunting** — peran yang memang bertanggung jawab.
 * 3. **Menghapus** — hanya administrator. Penghapusan tidak bisa dibatalkan
 *    dan tidak meninggalkan jejak; ini pagar yang paling murah untuk dipasang.
 */
abstract class PengurusPolicy
{
    /**
     * Peran selain administrator yang boleh menambah dan menyunting.
     *
     * @return list<string>
     */
    abstract protected function penanggungJawab(): array;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->berperan($this->penanggungJawab());
    }

    public function update(User $user): bool
    {
        return $user->berperan($this->penanggungJawab());
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isAdmin();
    }
}

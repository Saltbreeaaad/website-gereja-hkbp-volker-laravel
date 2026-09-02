<?php

namespace App\Policies;

use App\Models\User;

/**
 * Pengumuman penting tampil sebagai spanduk di puncak beranda publik, jadi
 * yang boleh menulisnya sama dengan yang boleh menulis warta: sekretaris.
 *
 * Tanpa berkas ini Filament melewatkan otorisasi sama sekali — `Resource::can()`
 * mengembalikan true begitu tidak ada policy untuk modelnya — sehingga
 * bendahara pun dapat mengubah dan menghapus pengumuman yang dibaca seluruh
 * jemaat.
 */
class PengumumanPentingPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

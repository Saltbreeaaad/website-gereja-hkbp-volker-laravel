<?php

namespace App\Policies;

use App\Models\User;

/** Jadwal ibadah disusun sekretariat. */
class JadwalIbadahPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

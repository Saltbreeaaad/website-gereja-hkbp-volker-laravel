<?php

namespace App\Policies;

use App\Models\User;

/** Permohonan pemakaian gedung ditinjau sekretariat. */
class PenggunaanGerejaPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

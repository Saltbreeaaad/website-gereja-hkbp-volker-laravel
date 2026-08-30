<?php

namespace App\Policies;

use App\Models\User;

/** Catatan keuangan adalah tanggung jawab bendahara. */
class KasGerejaPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::BENDAHARA];
    }
}

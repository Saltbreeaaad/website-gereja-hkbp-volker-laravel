<?php

namespace App\Policies;

use App\Models\User;

/** Renungan harian disiapkan sekretariat. */
class RenunganPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

<?php

namespace App\Policies;

use App\Models\User;

/** Warta jemaat disiapkan sekretariat. */
class WartaJemaatPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

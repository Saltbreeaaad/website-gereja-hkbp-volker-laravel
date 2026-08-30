<?php

namespace App\Policies;

use App\Models\User;

/** Data pelayan dan pengurus dikelola sekretariat. */
class ParhaladoPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

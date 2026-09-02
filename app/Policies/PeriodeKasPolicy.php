<?php

namespace App\Policies;

use App\Models\User;

class PeriodeKasPolicy extends PengurusPolicy
{
    protected function penanggungJawab(): array
    {
        return [User::BENDAHARA];
    }
}

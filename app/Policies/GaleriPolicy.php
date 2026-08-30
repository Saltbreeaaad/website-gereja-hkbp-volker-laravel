<?php

namespace App\Policies;

use App\Models\User;

/** Dokumentasi kegiatan dikelola sekretariat. */
class GaleriPolicy extends PengurusPolicy
{
    /** @return list<string> */
    protected function penanggungJawab(): array
    {
        return [User::SEKRETARIS];
    }
}

<?php

namespace App\Policies;

use App\Models\PermohonanDoa;
use App\Models\User;

class PermohonanDoaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->berperan([User::SEKRETARIS]);
    }

    public function view(User $user, PermohonanDoa $doa): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PermohonanDoa $doa): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, PermohonanDoa $doa): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

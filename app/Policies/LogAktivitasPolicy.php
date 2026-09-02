<?php

namespace App\Policies;

use App\Models\LogAktivitas;
use App\Models\User;

class LogAktivitasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, LogAktivitas $log): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LogAktivitas $log): bool
    {
        return false;
    }

    public function delete(User $user, LogAktivitas $log): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

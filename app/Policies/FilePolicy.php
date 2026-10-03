<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Setiap pengguna (termasuk Administrator) hanya boleh mengakses file miliknya sendiri.
 */
class FilePolicy
{
    private function owner(User $user, File $file): Response
    {
        return $user->id === $file->user_id ? Response::allow() : Response::denyAsNotFound();
    }

    public function view(User $user, File $file): Response
    {
        return $this->owner($user, $file);
    }

    public function download(User $user, File $file): Response
    {
        return $this->owner($user, $file);
    }

    public function delete(User $user, File $file): Response
    {
        return $this->owner($user, $file);
    }
}

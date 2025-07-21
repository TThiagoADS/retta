<?php

namespace App\Infrastructure\Repositories\User;

use App\Models\User as EloquentUser;
use App\Domain\Entities\User as DomainUser;
use App\Domain\Repositories\User\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(DomainUser $user): EloquentUser
    {
        return EloquentUser::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        ]);
    }
}

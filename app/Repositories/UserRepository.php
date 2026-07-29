<?php

namespace App\Repositories;

use App\Entities\UserEntity;
use App\Models\User;

class UserRepository
{
    public function __construct(private User $user) {}

    public function getById(int $id): ?UserEntity
    {
        $user = $this->user->with('wallet')->find($id);

        return UserEntity::fromArray($user->toArray());
    }

    public function getByEmailLogin(string $email): ?User
    {
        $user = $this->user
        ->select('id', 'name', 'email', 'password')
        ->with('wallet')
        ->where('email', $email)->first();

        return $user;
    }

    public function create(UserEntity $data): UserEntity
    {
        $user = $this->user->create($data->toArray());

        return UserEntity::fromArray($user->toArray());
    }

    public function update(int $id, UserEntity $data): UserEntity
    {
        $user = $this->user->find($id);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $user->update($data->toArray());

        return UserEntity::fromArray($user->toArray());
    }

    public function delete(int $id): bool
    {
        $user = $this->user->find($id);

        if (!$user) {
            throw new \Exception("User not found");
        }

        return $user->delete();
    }
}
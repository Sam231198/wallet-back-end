<?php

namespace App\Entities;

class UserEntity
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $email,
        public ?string $password,
        public ?WalletEntity $wallet
    ) {
        // Initialization code if needed
    }

    static public function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            wallet: isset($data['wallet']) ? WalletEntity::fromArray($data['wallet']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'wallet' => $this->wallet
        ];
    }
}
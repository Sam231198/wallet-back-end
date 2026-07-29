<?php

namespace App\Entities;

class WalletEntity
{
    public function __construct(
        public ?int $id,
        public ?int $user_id,
        public ?float $balance
    ) {
        // Initialization code if needed
    }

    static public function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: $data['user_id'] ?? null,
            balance: $data['balance'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'balance' => $this->balance
        ];
    }
}
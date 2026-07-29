<?php

namespace App\Entities;

class TransationEntity
{
    public function __construct(
        public ?int $id,
        public ?int $wallet_id,
        public ?float $amount,
        public ?string $description,
        public ?string $type, // 'income' or 'expense'
        public ?string $created_at,
        public ?int $wallet_transfer_id,
        public ?string $message,
    ) {
        // Initialization code if needed
    }

    static public function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            wallet_id: $data['wallet_id'] ?? null,
            wallet_transfer_id: $data['wallet_transfer_id'] ?? null,
            amount: $data['amount'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            message: $data['message'] ?? null,
            created_at: $data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'wallet_transfer_id' => $this->wallet_transfer_id,
            'amount' => $this->amount,
            'description' => $this->description,
            'type' => $this->type,
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}
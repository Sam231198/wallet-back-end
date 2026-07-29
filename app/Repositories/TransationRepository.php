<?php

namespace App\Repositories;

use App\Entities\TransationEntity;
use App\Models\Transation;

class TransationRepository
{
    public function __construct(private Transation $transation) {}

    public function getByWalletId(int $id): ?TransationEntity
    {
        $transaction = $this->transation->where('wallet_id', $id)->first();

        return $transaction ? TransationEntity::fromArray($transaction->toArray()) : null;
    }

    public function getListByWalletId(int $id, int $limit = 10, int $offset = 0): array
    {
        return $this->transation->where('wallet_id', $id)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($t) {
                return TransationEntity::fromArray($t->toArray());
            })->toArray();
    }

    public function getById(int $id): ?TransationEntity
    {
        return TransationEntity::fromArray($this->transation->find($id)->toArray());
    }

    public function create(TransationEntity $transactionEntity): TransationEntity
    {
        return TransationEntity::fromArray($this->transation->create($transactionEntity->toArray())->toArray());
    }

    public function update(int $id, TransationEntity $transactionEntity): TransationEntity
    {
        $wallet = $this->transation->find($id);

        if ($wallet) {
            $wallet->update($transactionEntity->toArray());
        } else {
            throw new \Exception("Transation not found");
        }

        return TransationEntity::fromArray($wallet->toArray());
    }

    public function delete(int $id): bool
    {
        $wallet = $this->transation->find($id);

        if (! $wallet) {
            throw new \Exception("Transation not found");
        }

        return (bool) $wallet->delete();
    }
}
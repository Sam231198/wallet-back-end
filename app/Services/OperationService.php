<?php

namespace App\Services;

use App\Entities\TransationEntity;
use App\Repositories\TransationRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationService
{
    public function __construct(
        private WalletRepository $walletRepository,
        private TransationRepository $transationRepository
    ) {}

    public function getHistory(int $userId): array
    {
        try {
            $wallet = $this->walletRepository->getByUserId($userId);

            if (! $wallet) {
                return [
                    'status' => 404,
                    'content' => 'Wallet not found',
                ];
            }

            return [
                'status' => 200,
                'content' => $this->transationRepository->getListByWalletId($wallet->id),
            ];
        } catch (\Exception $e) {
            Log::error('Get history failed: '.$e->getMessage());

            return [
                'status' => 500,
                'content' => 'Internal server error',
            ];
        }
    }

    public function deposit(int $userId, float $amount): array
    {
        try {
            return DB::transaction(function () use ($userId, $amount) {
                $wallet = $this->walletRepository->getByUserId($userId);

                if (! $wallet) {
                    return [
                        'status' => 404,
                        'content' => 'Wallet not found',
                    ];
                }

                $updatedWallet = $this->walletRepository->incrementBalance($wallet->id, $amount);

                $this->recordTransation($wallet->id, $amount, 'credit');

                return [
                    'status' => 201,
                    'content' => $updatedWallet,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Deposit failed: '.$e->getMessage());

            return [
                'status' => 500,
                'content' => 'Internal server error',
            ];
        }
    }

    public function withdraw(int $walletId, float $amount): array
    {
        try {
            return DB::transaction(function () use ($walletId, $amount) {
                $wallet = $this->walletRepository->getById($walletId);

                if (! $wallet) {
                    return [
                        'status' => 404,
                        'content' => 'Wallet not found',
                    ];
                }

                if ($wallet->balance < $amount) {
                    return [
                        'status' => 400,
                        'content' => 'Insufficient funds',
                    ];
                }

                $updatedWallet = $this->walletRepository->decrementBalance($walletId, $amount);

                $this->recordTransation($walletId, $amount, 'debit');

                return [
                    'status' => 201,
                    'content' => $updatedWallet,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Debit failed: '.$e->getMessage());

            return [
                'status' => 500,
                'content' => 'Internal server error',
            ];
        }
    }

    public function transfer(int $fromWalletId, int $toWalletId, float $amount): array
    {
        try {
            return DB::transaction(function () use ($fromWalletId, $toWalletId, $amount) {
                $fromWallet = $this->walletRepository->getById($fromWalletId);
                $toWallet = $this->walletRepository->getById($toWalletId);

                if (! $fromWallet || ! $toWallet) {
                    return [
                        'status' => 404,
                        'content' => 'One or both wallets not found',
                    ];
                }

                if ($fromWallet->balance < $amount) {
                    return [
                        'status' => 400,
                        'content' => 'Insufficient funds in the source wallet',
                    ];
                }

                $result = $this->walletRepository->tranfer($fromWalletId, $toWalletId, $amount);

                $this->recordTransation($fromWalletId, $amount, 'transfer', $toWalletId);

                return [
                    'status' => 201,
                    'content' => $result,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Transfer failed: '.$e->getMessage());

            return [
                'status' => 500,
                'content' => 'Internal server error',
            ];
        }
    }

    public function recoveryTransfer(int $transationId): array
    {
        $transation = $this->transationRepository->getById($transationId);

        if (! $transation) {
            return [
                'status' => 404,
                'content' => 'Transation not found',
            ];
        }

        try {

            $result = $this->walletRepository->tranfer($transation->wallet_transfer_id, $transation->wallet_id, $transation->amount);

            $this->recordTransation($transation->wallet_id, $transation->amount, 'reverse', $transation->wallet_transfer_id, 'Recovery transfer');

            return [
                'status' => 201,
                'content' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Recovery transfer failed: '.$e->getMessage());

            return [
                'status' => 500,
                'content' => 'Internal server error',
            ];
        }
    }

    private function recordTransation(int $walletId, float $amount, string $type, ?int $walletIdTransfer = null, string $message = ''): void
    {
        $transition = TransationEntity::fromArray([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'type' => $type,
            'wallet_transfer_id' => $walletIdTransfer,
            'message' => $message,
        ]);

        $this->transationRepository->create($transition);
    }
}

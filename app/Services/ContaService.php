<?php

namespace App\Services;

use App\Entities\UserEntity;
use App\Entities\WalletEntity;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ContaService
{
    public function __construct(
        private UserRepository $userRepository,
        private WalletRepository $walletRepository
    ) {}

    public function login(string $email, string $password): array
    {
        try {
            $user = $this->userRepository->getByEmailLogin($email);

            if (!$user || !Hash::check($password, $user->password)) {
                return [
                    'status' => 401,
                    'content' => 'Invalid credentials'
                ];
            }

            return [
                'status' => 201,
                'content' => [
                    'token' => $user->createToken('auth_token')->plainTextToken,
                    'user' => $user
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'content' => 'Internal server error'
            ];
        }
    }

    public function createUserWithWallet(array $userData): array
    {
        try {
            $userEntity = UserEntity::fromArray($userData);

            $userEntity = $this->userRepository->create($userEntity);

            $walletEntity = new WalletEntity(null, $userEntity->id, 0.00);
            $userEntity->wallet = $this->walletRepository->create($walletEntity);

            return [
                'status' => 201,
                'content' => $userEntity
            ];
        } catch (\Exception $e) {
            Log::error('Create user failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'content' => 'Internal server error'
            ];
        }
    }

    public function getUserById(int $id): array
    {
        try {
            $user = $this->userRepository->getById($id);

            return [
                'status' => 200,
                'content' => $user
            ];
        } catch (\Exception $e) {
            Log::error('Get user failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'content' => 'Internal server error'
            ];
        }
    }

    public function updateUserWithWallet(int $id, array $userData, array $walletData): array
    {
        try {
            $user = UserEntity::fromArray($userData);
            $user = $this->userRepository->update($id, $user);

            $wallet = $this->walletRepository->getById($user->id);
            if ($wallet) {
                $wallet = WalletEntity::fromArray($walletData);
                $this->walletRepository->update($wallet->id, $wallet);
            }

            return [
                'status' => 200,
                'content' => $user
            ];
        } catch (\Exception $e) {
            Log::error('Update user failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'content' => 'Internal server error'
            ];
        }
    }

    public function deleteUserWithWallet(int $id): array
    {
        try {
            $wallet = $this->walletRepository->getById($id);
            if ($wallet) {
                $this->walletRepository->delete($wallet->id);
            }

            return [
                'status' => 204,
                'content' => null
            ];
        } catch (\Exception $e) {
            Log::error('Delete user failed: ' . $e->getMessage());
            return [
                'status' => 500,
                'content' => 'Internal server error'
            ];
        }
    }
}

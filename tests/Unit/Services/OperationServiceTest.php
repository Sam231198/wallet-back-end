<?php

namespace Tests\Unit\Services;

use App\Entities\WalletEntity;
use App\Repositories\TransationRepository;
use App\Repositories\WalletRepository;
use App\Services\OperationService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationServiceTest extends TestCase
{
    public function test_get_history_returns_404_when_wallet_not_found()
    {
        $walletId = 99;

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn(null);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->getHistory($walletId);

        $this->assertSame(404, $result['status']);
        $this->assertSame('Wallet not found', $result['content']);
    }

    public function test_get_history_returns_200_when_wallet_exists()
    {
        $walletId = 1;
        $wallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 100.0]);
        $transactions = [[ 'id' => 1, 'wallet_id' => $walletId, 'amount' => 50.0 ]];

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn($wallet);

        $transationRepo->expects($this->once())
            ->method('getListByWalletId')
            ->with($walletId)
            ->willReturn($transactions);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->getHistory($walletId);

        $this->assertSame(200, $result['status']);
        $this->assertSame($transactions, $result['content']);
    }

    public function test_deposit_returns_404_when_wallet_not_found()
    {
        $walletId = 5;
        $amount = 20.0;

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn(null);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->deposit($walletId, $amount);

        $this->assertSame(404, $result['status']);
        $this->assertSame('Wallet not found', $result['content']);
    }

    public function test_deposit_returns_201_on_success()
    {
        $walletId = 5;
        $amount = 20.0;
        $wallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 100.0]);
        $updatedWallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 120.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn($wallet);

        $walletRepo->expects($this->once())
            ->method('update')
            ->with($walletId, $this->callback(fn($value) => $value->balance === 120.0))
            ->willReturn($updatedWallet);

        $transationRepo->expects($this->once())
            ->method('create')
            ->with($this->callback(fn($entity) => $entity->wallet_id === $walletId && $entity->amount === $amount));

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->deposit($walletId, $amount);

        $this->assertSame(201, $result['status']);
        $this->assertSame($updatedWallet, $result['content']);
    }

    public function test_deposit_returns_500_when_update_throws_exception()
    {
        $walletId = 5;
        $amount = 20.0;
        $wallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 100.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn($wallet);

        $walletRepo->expects($this->once())
            ->method('update')
            ->willThrowException(new \Exception('update fail'));

        $transationRepo->expects($this->once())
            ->method('create');

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->deposit($walletId, $amount);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_withdraw_returns_404_when_wallet_not_found()
    {
        $walletId = 6;
        $amount = 50.0;

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn(null);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->withdraw($walletId, $amount);

        $this->assertSame(404, $result['status']);
        $this->assertSame('Wallet not found', $result['content']);
    }

    public function test_withdraw_returns_400_when_insufficient_funds()
    {
        $walletId = 6;
        $amount = 150.0;
        $wallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 100.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn($wallet);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->withdraw($walletId, $amount);

        $this->assertSame(400, $result['status']);
        $this->assertSame('Insufficient funds', $result['content']);
    }

    public function test_withdraw_returns_201_on_success()
    {
        $walletId = 6;
        $amount = 50.0;
        $wallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 100.0]);
        $updatedWallet = WalletEntity::fromArray(['id' => $walletId, 'balance' => 50.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($walletId)
            ->willReturn($wallet);

        $walletRepo->expects($this->once())
            ->method('update')
            ->with($walletId, $this->callback(fn($value) => $value->balance === 50.0))
            ->willReturn($updatedWallet);

        $transationRepo->expects($this->once())
            ->method('create');

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->withdraw($walletId, $amount);

        $this->assertSame(201, $result['status']);
        $this->assertSame($updatedWallet, $result['content']);
    }

    public function test_transfer_returns_404_when_wallet_missing()
    {
        $fromId = 7;
        $toId = 8;
        $amount = 10.0;

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->exactly(2))
            ->method('getById')
            ->willReturnOnConsecutiveCalls(null, null);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->transfer($fromId, $toId, $amount);

        $this->assertSame(404, $result['status']);
        $this->assertSame('One or both wallets not found', $result['content']);
    }

    public function test_transfer_returns_400_when_insufficient_funds()
    {
        $fromId = 7;
        $toId = 8;
        $amount = 150.0;
        $fromWallet = WalletEntity::fromArray(['id' => $fromId, 'balance' => 100.0]);
        $toWallet = WalletEntity::fromArray(['id' => $toId, 'balance' => 50.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->exactly(2))
            ->method('getById')
            ->willReturnOnConsecutiveCalls($fromWallet, $toWallet);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->transfer($fromId, $toId, $amount);

        $this->assertSame(400, $result['status']);
        $this->assertSame('Insufficient funds in the source wallet', $result['content']);
    }

    public function test_transfer_returns_201_on_success()
    {
        $fromId = 7;
        $toId = 8;
        $amount = 50.0;
        $fromWallet = WalletEntity::fromArray(['id' => $fromId, 'balance' => 100.0]);
        $toWallet = WalletEntity::fromArray(['id' => $toId, 'balance' => 50.0]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $walletRepo->expects($this->exactly(2))
            ->method('getById')
            ->willReturnOnConsecutiveCalls($fromWallet, $toWallet);

        $walletRepo->expects($this->once())
            ->method('tranfer')
            ->with($fromId, $toId, $amount)
            ->willReturn(true);

        $transationRepo->expects($this->exactly(2))
            ->method('create');

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->transfer($fromId, $toId, $amount);

        $this->assertSame(201, $result['status']);
        $this->assertTrue($result['content']);
    }

    public function test_recovery_transfer_returns_404_when_transaction_not_found()
    {
        $transationId = 10;

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        $transationRepo->expects($this->once())
            ->method('getById')
            ->with($transationId)
            ->willReturn(null);

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->recoveryTransfer($transationId);

        $this->assertSame(404, $result['status']);
        $this->assertSame('Transation not found', $result['content']);
    }

    public function test_recovery_transfer_returns_201_on_success()
    {
        $transationId = 10;
        $transation = \App\Entities\TransationEntity::fromArray([
            'id' => $transationId,
            'wallet_id' => 2,
            'wallet_transfer_id' => 3,
            'amount' => 20.0,
            'type' => 'transfer',
            'description' => null,
            'message' => null,
            'created_at' => null,
        ]);

        $walletRepo = $this->createMock(WalletRepository::class);
        $transationRepo = $this->createMock(TransationRepository::class);

        $transationRepo->expects($this->once())
            ->method('getById')
            ->with($transationId)
            ->willReturn($transation);

        $walletRepo->expects($this->once())
            ->method('tranfer')
            ->with($transation->wallet_transfer_id, $transation->wallet_id, $transation->amount)
            ->willReturn(true);

        $transationRepo->expects($this->exactly(2))
            ->method('create');

        $service = new OperationService($walletRepo, $transationRepo);
        $result = $service->recoveryTransfer($transationId);

        $this->assertSame(201, $result['status']);
        $this->assertTrue($result['content']);
    }
}

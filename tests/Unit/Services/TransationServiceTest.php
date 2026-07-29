<?php

namespace Tests\Unit\Services;

use App\Entities\TransationEntity;
use App\Repositories\TransationRepository;
use App\Services\TransationService;
use Tests\TestCase;

class TransationServiceTest extends TestCase
{
    public function test_extract_returns_200_on_success()
    {
        $walletId = 5;
        $expected = TransationEntity::fromArray(['id' => 1, 'wallet_id' => $walletId, 'amount' => 50.0, 'type' => 'deposit', 'description' => null, 'message' => null, 'created_at' => null]);

        $transationRepo = $this->createMock(TransationRepository::class);
        $transationRepo->expects($this->once())
            ->method('getByWalletId')
            ->with($walletId)
            ->willReturn($expected);

        $service = new TransationService($transationRepo);
        $result = $service->extract($walletId);

        $this->assertSame(200, $result['status']);
        $this->assertSame($expected, $result['content']);
    }

    public function test_extract_returns_500_on_exception()
    {
        $walletId = 5;

        $transationRepo = $this->createMock(TransationRepository::class);
        $transationRepo->expects($this->once())
            ->method('getByWalletId')
            ->with($walletId)
            ->willThrowException(new \Exception('fail'));

        $service = new TransationService($transationRepo);
        $result = $service->extract($walletId);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_list_returns_200_on_success()
    {
        $walletId = 5;
        $expected = [
            ['id' => 1, 'wallet_id' => $walletId, 'amount' => 10.0],
        ];

        $transationRepo = $this->createMock(TransationRepository::class);
        $transationRepo->expects($this->once())
            ->method('getListByWalletId')
            ->with($walletId, 10, 0)
            ->willReturn($expected);

        $service = new TransationService($transationRepo);
        $result = $service->list($walletId);

        $this->assertSame(200, $result['status']);
        $this->assertSame($expected, $result['content']);
    }

    public function test_list_returns_500_on_exception()
    {
        $walletId = 5;

        $transationRepo = $this->createMock(TransationRepository::class);
        $transationRepo->expects($this->once())
            ->method('getListByWalletId')
            ->with($walletId, 10, 0)
            ->willThrowException(new \Exception('fail'));

        $service = new TransationService($transationRepo);
        $result = $service->list($walletId);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }
}

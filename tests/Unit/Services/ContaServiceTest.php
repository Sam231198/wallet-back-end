<?php

namespace Tests\Unit\Services;

use App\Entities\UserEntity;
use App\Entities\WalletEntity;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\ContaService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContaServiceTest extends TestCase
{
    public function test_login_returns_401_when_user_not_found()
    {
        $email = 'user@example.com';
        $password = 'secret';

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getByEmailLogin')
            ->with($email)
            ->willReturn(null);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->login($email, $password);

        $this->assertSame(401, $result['status']);
        $this->assertSame('Invalid credentials', $result['content']);
    }

    public function test_login_returns_401_when_password_is_invalid()
    {
        $email = 'user@example.com';
        $password = 'secret';
        $invalidPassword = 'wrong';
        $hash = Hash::make($password);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createToken'])
            ->getMock();
        $user->password = $hash;
        $user->method('createToken')->willReturn((object)['plainTextToken' => 'token123']);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getByEmailLogin')
            ->with($email)
            ->willReturn($user);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->login($email, $invalidPassword);

        $this->assertSame(401, $result['status']);
        $this->assertSame('Invalid credentials', $result['content']);
    }

    public function test_login_returns_201_and_token_on_success()
    {
        $email = 'user@example.com';
        $password = 'secret';
        $hash = Hash::make($password);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createToken'])
            ->getMock();
        $user->password = $hash;
        $user->method('createToken')->willReturn((object)['plainTextToken' => 'token123']);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getByEmailLogin')
            ->with($email)
            ->willReturn($user);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->login($email, $password);

        $this->assertSame(201, $result['status']);
        $this->assertSame('token123', $result['content']['token']);
    }

    public function test_login_returns_500_when_repository_throws()
    {
        $email = 'user@example.com';
        $password = 'secret';

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getByEmailLogin')
            ->with($email)
            ->willThrowException(new \Exception('fail'));

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->login($email, $password);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_create_user_with_wallet_returns_201_on_success()
    {
        $userData = ['name' => 'Teste', 'email' => 'teste@example.com', 'password' => 'secret'];
        $createdUser = UserEntity::fromArray(['id' => 1, 'name' => 'Teste', 'email' => 'teste@example.com', 'password' => 'secret']);
        $wallet = WalletEntity::fromArray(['id' => 1, 'user_id' => 1, 'balance' => 0.0]);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('create')
            ->willReturn($createdUser);

        $walletRepo->expects($this->once())
            ->method('create')
            ->willReturn($wallet);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->createUserWithWallet($userData);

        $this->assertSame(201, $result['status']);
        $this->assertInstanceOf(UserEntity::class, $result['content']);
        $this->assertSame($wallet, $result['content']->wallet);
    }

    public function test_create_user_with_wallet_returns_500_on_exception()
    {
        $userData = ['name' => 'Teste', 'email' => 'teste@example.com', 'password' => 'secret'];

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('fail'));

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->createUserWithWallet($userData);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_get_user_by_id_returns_200_on_success()
    {
        $id = 10;
        $user = UserEntity::fromArray(['id' => $id, 'name' => 'Teste']);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($user);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->getUserById($id);

        $this->assertSame(200, $result['status']);
        $this->assertSame($user, $result['content']);
    }

    public function test_get_user_by_id_returns_500_on_exception()
    {
        $id = 10;

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willThrowException(new \Exception('fail'));

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->getUserById($id);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_update_user_with_wallet_returns_200_when_wallet_exists()
    {
        $id = 10;
        $userData = ['id' => $id, 'name' => 'Atualizado'];
        $walletData = ['id' => 5, 'user_id' => $id, 'balance' => 10.0];

        $updatedUser = UserEntity::fromArray(['id' => $id, 'name' => 'Atualizado']);
        $wallet = WalletEntity::fromArray($walletData);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('update')
            ->with($id, $this->callback(fn($value) => $value instanceof UserEntity))
            ->willReturn($updatedUser);

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($wallet);

        $walletRepo->expects($this->once())
            ->method('update')
            ->with($wallet->id, $this->callback(fn($value) => $value instanceof WalletEntity))
            ->willReturn($wallet);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->updateUserWithWallet($id, $userData, $walletData);

        $this->assertSame(200, $result['status']);
        $this->assertSame($updatedUser, $result['content']);
    }

    public function test_update_user_with_wallet_returns_500_on_exception()
    {
        $id = 10;
        $userData = ['id' => $id, 'name' => 'Atualizado'];
        $walletData = ['id' => 5, 'user_id' => $id, 'balance' => 10.0];

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $userRepo->expects($this->once())
            ->method('update')
            ->with($id, $this->callback(fn($value) => $value instanceof UserEntity))
            ->willThrowException(new \Exception('fail'));

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->updateUserWithWallet($id, $userData, $walletData);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }

    public function test_delete_user_with_wallet_returns_204_when_wallet_missing()
    {
        $id = 10;

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn(null);

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->deleteUserWithWallet($id);

        $this->assertSame(204, $result['status']);
        $this->assertNull($result['content']);
    }

    public function test_delete_user_with_wallet_returns_500_on_exception()
    {
        $id = 10;
        $wallet = WalletEntity::fromArray(['id' => 4, 'user_id' => $id]);

        $userRepo = $this->createMock(UserRepository::class);
        $walletRepo = $this->createMock(WalletRepository::class);

        $walletRepo->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($wallet);

        $walletRepo->expects($this->once())
            ->method('delete')
            ->with($wallet->id)
            ->willThrowException(new \Exception('fail'));

        $service = new ContaService($userRepo, $walletRepo);
        $result = $service->deleteUserWithWallet($id);

        $this->assertSame(500, $result['status']);
        $this->assertSame('Internal server error', $result['content']);
    }
}

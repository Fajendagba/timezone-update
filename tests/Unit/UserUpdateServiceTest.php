<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserUpdateService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class UserUpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserUpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserUpdateService();
    }

    /** @test */
    public function it_correctly_batches_users()
    {
        Log::shouldReceive('info')->once()->withArgs(function($message, $payload) {
            return $message === 'Batch update payload:' &&
                   count($payload['batches'][0]['subscribers']) === 2;
        });

        $users = [
            $this->createUser('john@example.com', 'UTC', 'John', 'Doe'),
            $this->createUser('jane@example.com', 'UTC', 'Jane', 'Smith')
        ];

        $this->service->updateUsers($users);

        $this->assertEquals(1, $this->service->getBatchesMade());
    }

    /** @test */
    public function it_handles_empty_user_list()
    {
        Log::shouldReceive('info')->never();

        $this->service->updateUsers([]);

        $this->assertEquals(0, $this->service->getBatchesMade());
    }

    /** @test */
    public function it_splits_users_into_multiple_batches_when_exceeding_batch_size()
    {
        $users = [];
        for ($i = 0; $i < 1500; $i++) {
            $users[] = $this->createUser(
                "user{$i}@example.com",
                'UTC',
                "User{$i}",
                "LastName{$i}"
            );
        }

        Log::shouldReceive('info')->twice();

        $this->service->updateUsers($users);

        $this->assertEquals(2, $this->service->getBatchesMade());
    }

    /** @test */
    public function it_logs_error_when_batch_update_fails()
    {
        Log::shouldReceive('info')->andThrow(new \Exception('API Error'));
        Log::shouldReceive('error')->once()->withArgs(function($message, $data) {
            return $message === 'Batch update failed' &&
                   $data['error'] === 'API Error' &&
                   $data['batch_size'] === 1;
        });

        $users = [$this->createUser('john@example.com', 'UTC', 'John', 'Doe')];

        $this->expectException(\Exception::class);
        $this->service->updateUsers($users);
    }

    private function createUser(string $email, string $timezone, string $firstname, string $lastname)
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('setAttribute')->andReturnSelf();

        $user->shouldReceive('getAttribute')->with('email')->andReturn($email);
        $user->shouldReceive('getAttribute')->with('timezone')->andReturn($timezone);
        $user->shouldReceive('getAttribute')->with('firstname')->andReturn($firstname);
        $user->shouldReceive('getAttribute')->with('lastname')->andReturn($lastname);

        $user->email = $email;
        $user->timezone = $timezone;
        $user->firstname = $firstname;
        $user->lastname = $lastname;

        return $user;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

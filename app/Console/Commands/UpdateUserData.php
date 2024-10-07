<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserUpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-user-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update users firstname, lastname, and timezone';

    private const TIMEZONES = ['CET', 'CST', 'GMT+1'];

    protected UserUpdateService $userUpdateService;

    public function __construct(UserUpdateService $userUpdateService)
    {
        parent::__construct();
        $this->userUpdateService = $userUpdateService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting user data update...');

        DB::beginTransaction();

        try {
            // Instead of using User::all(), I'm using chunk here in case of large data
            $usersToUpdate = [];

            User::query()
                ->chunkById(100, function ($users) use (&$usersToUpdate) {
                    foreach ($users as $user) {
                        $newFirstname = fake()->firstName();
                        $newLastname = fake()->lastName();
                        $newTimezone = self::TIMEZONES[array_rand(self::TIMEZONES)];

                        if ($user->firstname !== $newFirstname || $user->lastname !== $newLastname || $user->timezone !== $newTimezone) {
                            $user->update([
                                'firstname' => $newFirstname,
                                'lastname' => $newLastname,
                                'timezone' => $newTimezone
                            ]);
                            $usersToUpdate[] = $user;
                        }
                    }
                });

            $this->userUpdateService->updateUsers($usersToUpdate);

            DB::commit();
            $this->info('User data updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}

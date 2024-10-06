<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting user data update...');

        DB::beginTransaction();

        try {
            // Instead of using User::all(), I'm using chunk here in case of large data
            User::query()
                ->chunkById(100, function ($users) {
                    foreach ($users as $user) {
                        $user->update([
                            'firstname' => fake()->firstName(),
                            'lastname' => fake()->lastName(),
                            'timezone' => self::TIMEZONES[array_rand(self::TIMEZONES)]
                        ]);
                    }
                });

            DB::commit();
            $this->info('User data updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}

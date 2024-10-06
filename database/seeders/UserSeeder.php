<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timezones = ['CET', 'CST', 'GMT+1'];

        User::factory()
            ->count(20)
            ->sequence(fn ($sequence) =>[
                'timezone' => $timezones[array_rand($timezones)]
            ])
            ->create();
    }
}

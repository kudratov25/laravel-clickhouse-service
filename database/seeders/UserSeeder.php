<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = [];
        $batchSize = 10000;

        DB::connection()->disableQueryLog();

        DB::beginTransaction();

        try {
            for ($i = 0; $i < 150000; $i++) {
                $users[] = [
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($users) >= $batchSize) {
                    DB::table('users')->insert($users);
                    $users = [];
                }
            }

            if (count($users) > 0) {
                DB::table('users')->insert($users);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error seeding users: ' . $e->getMessage());
        }

        DB::connection()->enableQueryLog();
    }
}

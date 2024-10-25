<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('members')->insert([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@doe.com',
            'phone_number' => '09123456789',
            'sex' => 'Male',
            'goal' => 'Lose Weight',
            'current_weight' => '80',
            'target_weight' => '70',
        ]);
    }
}

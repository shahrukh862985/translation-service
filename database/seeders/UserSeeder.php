<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //user insert if not exists
        DB::table('users')->updateOrInsert([
            'email' => "shahrukh@test.com",
        ], [
            'name' => "Shahrukh Khan",
            'password' => Hash::make('Abacus@324'),
        ]);
    }
}

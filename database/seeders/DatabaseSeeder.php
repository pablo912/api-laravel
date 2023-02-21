<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Company::create([
            'client_id' => 'a4378530-d6e1-49c7-ac54-9512aa78e113',
            'client_secret' => '8DZ9Q8hzileh3B1m885+Pw==',
            'ruc'=>'20529299380',
            'usuario_sol'=>'ASSITYLE',
            'clave_sol'=>'hailumedi',

        ]);

        \App\Models\Plan::create([
            'description' => 'Basico',
        ]);
        \App\Models\Plan::create([
            'description' => 'Empresarial',
        ]);
        \App\Models\Plan::create([
            'description' => 'Premium',
        ]);

        \App\Models\User::create([

            'name' => 'Pablo',
            'surname' => 'Santiago',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'verification_token' => User::generarVerificationToken(),
            'plan_id' => 3,
            'expiration_date' => Carbon::now()->addYear()->format('Y-m-d')

        ]);

 
    }
}

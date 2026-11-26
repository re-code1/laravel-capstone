<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

// One login per teacher, plus one admin (the registrar).
// Every password is "password". Fine for a lab, never for a real school.
// Run it after SchoolSeeder:  php artisan db:seed --class=UserSeeder
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Registrar',
            'email' => 'registrar@school.edu.ph',
            'password' => 'password',
        ]);
        $admin->role = 'admin';
        $admin->save();

        foreach (Teacher::all() as $teacher) {
            $user = User::create([
                'name' => $teacher->fullName(),
                'email' => $teacher->email,
                'password' => 'password',
            ]);
            $user->teacher_id = $teacher->id;
            $user->save();
        }
    }
}
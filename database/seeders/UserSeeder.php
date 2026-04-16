<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fixedUsers = [
            [
                'email' => 'superadmin@orient-togo.tg',
                'role' => UserRoleEnum::Superadmin,
            ],
            [
                'email' => 'admin@orient-togo.tg',
                'role' => UserRoleEnum::Admin,
            ],
            [
                'email' => 'editor@orient-togo.tg',
                'role' => UserRoleEnum::Editor,
            ],
            [
                'email' => 'conseiller@orient-togo.tg',
                'role' => UserRoleEnum::Advisor,
            ],
        ];

        foreach ($fixedUsers as $userData) {
            $user = User::factory()->create([
                'email' => $userData['email'],
                'role' => $userData['role'],
            ]);

            // Assign spatie role
            $user->assignRole($userData['role']->value);

            UserProfile::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        // Create 20 random students
        $students = User::factory()->count(20)->student()->create();
        foreach ($students as $student) {
            $student->assignRole(UserRoleEnum::Student->value);
            UserProfile::factory()->create([
                'user_id' => $student->id,
            ]);
        }
    }
}

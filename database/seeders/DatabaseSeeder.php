<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Ensure 'student' role exists for 'web' guard
        if (!Role::where('name', 'student')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'student', 'guard_name' => 'web']);
        }

        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SerieSeeder::class,
            GrowthSectorSeeder::class,
            CareerSeeder::class,
            EstablishmentSeeder::class,
            FieldSeeder::class,
            CourseSeeder::class,
            DomainRelationsSeeder::class,
        ]);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

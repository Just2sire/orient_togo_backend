<?php

namespace Database\Seeders;

use App\Enums\LevelEnum;
use App\Models\Establishment;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $ul = Establishment::where('name', 'Université de Lomé (UL)')->first();

        if ($ul) {
            $courses = [
                [
                    'name' => 'Licence Génie Logiciel',
                    'level' => LevelEnum::Licence,
                    'description' => 'Formation pratique en développement logiciel.',
                    'duration_months' => 36,
                    'annual_fees' => 30000,
                ],
                [
                    'name' => 'Doctorat en Médecine',
                    'level' => LevelEnum::Doctorat,
                    'description' => 'Huit années d\'études pour devenir médecin.',
                    'duration_months' => 96,
                    'annual_fees' => 50000,
                ],
            ];

            foreach ($courses as $data) {
                $ul->courses()->create($data);
            }
        }
    }
}

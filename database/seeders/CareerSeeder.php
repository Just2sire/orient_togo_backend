<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\GrowthSector;
use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $tech = GrowthSector::where('name', 'Technologie et Numérique')->first();
        $sante = GrowthSector::where('name', 'Santé et Social')->first();

        $careers = [
            [
                'name' => 'Développeur Fullstack',
                'description' => 'Conçoit et développe des applications web de A à Z.',
                'required_skills' => ['PHP', 'JavaScript', 'SQL', 'Git'],
                'market_demand' => 9,
                'salary_min' => 300000,
                'salary_max' => 800000,
                'is_promising' => true,
                'growth_sector_id' => $tech?->id,
            ],
            [
                'name' => 'Médecin Généraliste',
                'description' => 'Prend en charge le suivi durable et les soins médicaux généraux.',
                'required_skills' => ['Diagnostic', 'Empathie', 'Rigueur'],
                'market_demand' => 10,
                'salary_min' => 500000,
                'salary_max' => 1200000,
                'is_promising' => true,
                'growth_sector_id' => $sante?->id,
            ],
        ];

        foreach ($careers as $data) {
            Career::create($data);
        }
    }
}

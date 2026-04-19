<?php

namespace Database\Seeders;

use App\Models\GrowthSector;
use Illuminate\Database\Seeder;

class GrowthSectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            [
                'name' => 'Technologie et Numérique',
                'description' => 'Secteur en pleine expansion porté par la stratégie Togo 2025.',
                'color_hex' => '#0066FF',
                'icon_code' => 'laptop',
                'annual_growth' => 12.5,
            ],
            [
                'name' => 'Agrobusiness',
                'description' => 'Moteur de l\'économie togolaise avec une forte demande en transformation.',
                'color_hex' => '#28A745',
                'icon_code' => 'leaf',
                'annual_growth' => 8.2,
            ],
            [
                'name' => 'Santé et Social',
                'description' => 'Besoin croissant en personnel qualifié pour les infrastructures sanitaires.',
                'color_hex' => '#DC3545',
                'icon_code' => 'heartbeat',
                'annual_growth' => 5.0,
            ],
            [
                'name' => 'Logistique et Transports',
                'description' => 'Le Togo comme hub logistique régional avec le Port Autonome de Lomé.',
                'color_hex' => '#FFC107',
                'icon_code' => 'ship',
                'annual_growth' => 10.1,
            ],
        ];

        foreach ($sectors as $data) {
            GrowthSector::create($data);
        }
    }
}

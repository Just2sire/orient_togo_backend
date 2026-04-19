<?php

namespace Database\Seeders;

use App\Enums\EstablishmentTypeEnum;
use App\Enums\RegionEnum;
use App\Models\Establishment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EstablishmentSeeder extends Seeder
{
    public function run(): void
    {
        $establishments = [
            [
                'name' => 'Université de Lomé (UL)',
                'type' => EstablishmentTypeEnum::Universite,
                'region' => RegionEnum::Maritime,
                'city' => 'Lomé',
                'address' => 'Boulevard Gnassingbé Eyadéma, Lomé',
                'description' => 'La plus grande université publique du Togo.',
                'website' => 'https://www.univ-lome.tg',
                'is_public' => true,
                'is_verified' => true,
                'verification_status' => 'approved',
                'is_selected' => true,
            ],
            [
                'name' => 'Université de Kara (UK)',
                'type' => EstablishmentTypeEnum::Universite,
                'region' => RegionEnum::Kara,
                'city' => 'Kara',
                'address' => 'Kara, Togo',
                'description' => 'Deuxième université publique du pays.',
                'website' => 'https://www.univ-kara.tg',
                'is_public' => true,
                'is_verified' => true,
                'verification_status' => 'approved',
                'is_selected' => true,
            ],
            [
                'name' => 'Lycée de Tokoin',
                'type' => EstablishmentTypeEnum::Lycee,
                'region' => RegionEnum::Maritime,
                'city' => 'Lomé',
                'address' => 'Tokoin, Lomé',
                'description' => 'Un des plus anciens lycées de la capitale.',
                'is_public' => true,
                'is_verified' => true,
                'verification_status' => 'approved',
                'is_selected' => true,
            ],
            [
                'name' => 'IAEC (Institut Africain d\'Enseignement Commercial)',
                'type' => EstablishmentTypeEnum::EcolePrivee,
                'region' => RegionEnum::Maritime,
                'city' => 'Lomé',
                'address' => 'Lomé, Togo',
                'description' => 'Institut privé d\'enseignement supérieur.',
                'website' => 'https://www.iaec-togo.com',
                'is_public' => false,
                'is_verified' => true,
                'verification_status' => 'approved',
                'is_selected' => true,
            ],
        ];

        foreach ($establishments as $data) {
            $data['slug'] = Str::slug($data['name']);
            Establishment::create($data);
        }
    }
}

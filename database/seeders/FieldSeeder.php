<?php

namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            [
                'name' => 'Médecine et Santé',
                'description' => 'Études médicales, maïeutique, pharmacie et paramédical.',
                'estimated_duration_years' => 7.0,
                'main_domain' => 'sante',
                'is_selected' => true,
            ],
            [
                'name' => 'Génie Logiciel et Informatique',
                'description' => 'Développement d\'applications, systèmes et réseaux.',
                'estimated_duration_years' => 3.0,
                'main_domain' => 'ingenierie',
                'is_selected' => true,
            ],
            [
                'name' => 'Droit et Sciences Juridiques',
                'description' => 'Formation des futurs avocats, juges et notaires.',
                'estimated_duration_years' => 5.0,
                'main_domain' => 'droit',
                'is_selected' => true,
            ],
            [
                'name' => 'Agronomie et Agro-industrie',
                'description' => 'Transformation agricole et gestion des exploitations.',
                'estimated_duration_years' => 5.0,
                'main_domain' => 'agriculture',
                'is_selected' => true,
            ],
            [
                'name' => 'Comptabilité et Audit',
                'description' => 'Gestion financière et expertise comptable.',
                'estimated_duration_years' => 3.0,
                'main_domain' => 'commerce',
                'is_selected' => true,
            ],
        ];

        foreach ($fields as $data) {
            Field::create($data);
        }
    }
}

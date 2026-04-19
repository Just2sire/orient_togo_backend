<?php

namespace Database\Seeders;

use App\Models\Serie;
use Illuminate\Database\Seeder;

class SerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = [
            [
                'code' => 'A',
                'label' => 'Lettres et Sciences Humaines',
                'description' => 'Pour les passionnés de langues, littérature, histoire...',
                'minimum_average' => 10.0,
                'required_profile' => 'Fort en français, anglais, histoire-géographie',
                'after_bac' => 'Droit, Lettres, Journalisme, Sciences Humaines, Tourisme',
                'order' => 1,
            ],
            [
                'code' => 'C',
                'label' => 'Mathématiques et Sciences Physiques',
                'description' => 'Pour les futurs ingénieurs, médecins, informaticiens...',
                'minimum_average' => 12.0,
                'required_profile' => 'Excellence en mathématiques et physique-chimie',
                'after_bac' => 'Médecine, Ingénierie, Informatique, Pharmacie',
                'order' => 2,
            ],
            [
                'code' => 'D',
                'label' => 'Sciences de la Vie et de la Terre',
                'description' => 'Pour les futurs biologistes, médecins, agronomes...',
                'minimum_average' => 11.0,
                'required_profile' => 'Fort en SVT, chimie, mathématiques acceptables',
                'after_bac' => 'Médecine, Pharmacie, Vétérinaire, Agronomie',
                'order' => 3,
            ],
            [
                'code' => 'F1',
                'label' => 'Construction Mécanique',
                'description' => 'Spécialisation dans la conception et fabrication mécanique.',
                'minimum_average' => 10.5,
                'required_profile' => 'Goût pour la mécanique, dessin industriel, physique.',
                'after_bac' => 'Génie Mécanique, Maintenance, BTS Industriels',
                'order' => 4,
            ],
            [
                'code' => 'G2',
                'label' => 'Gestion et Comptabilité',
                'description' => 'Pour les métiers de la finance, audit et gestion d\'entreprise.',
                'minimum_average' => 10.5,
                'required_profile' => 'Aptitude pour les chiffres, rigueur, organisation.',
                'after_bac' => 'Comptabilité, Finance, Audit, Gestion des Entreprises',
                'order' => 5,
            ],
        ];

        foreach ($series as $data) {
            $serie = Serie::create($data);
            $this->attachCoefficients($serie);
        }
    }

    private function attachCoefficients(Serie $serie): void
    {
        $coefficients = match ($serie->code) {
            'A' => [
                ['subject_name' => 'Français', 'coefficient' => 4],
                ['subject_name' => 'Anglais', 'coefficient' => 3],
                ['subject_name' => 'Histoire-Géographie', 'coefficient' => 3],
                ['subject_name' => 'Philosophie', 'coefficient' => 2],
            ],
            'C' => [
                ['subject_name' => 'Mathématiques', 'coefficient' => 7, 'minimum_grade' => 8.0],
                ['subject_name' => 'Physique-Chimie', 'coefficient' => 5],
                ['subject_name' => 'Français', 'coefficient' => 2],
                ['subject_name' => 'SVT', 'coefficient' => 2],
            ],
            'D' => [
                ['subject_name' => 'SVT', 'coefficient' => 6, 'minimum_grade' => 10.0],
                ['subject_name' => 'Mathématiques', 'coefficient' => 4],
                ['subject_name' => 'Physique-Chimie', 'coefficient' => 3],
                ['subject_name' => 'Français', 'coefficient' => 2],
            ],
            'F1' => [
                ['subject_name' => 'Construction Mécanique', 'coefficient' => 6],
                ['subject_name' => 'Mathématiques', 'coefficient' => 4],
                ['subject_name' => 'Physique', 'coefficient' => 3],
            ],
            'G2' => [
                ['subject_name' => 'Comptabilité', 'coefficient' => 6],
                ['subject_name' => 'Mathématiques', 'coefficient' => 4],
                ['subject_name' => 'Économie', 'coefficient' => 3],
            ],
            default => [],
        };

        foreach ($coefficients as $coeff) {
            $serie->subjectCoefficients()->create($coeff);
        }
    }
}

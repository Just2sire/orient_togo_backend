<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Course;
use App\Models\Establishment;
use App\Models\Field;
use App\Models\GrowthSector;
use App\Models\Serie;
use Illuminate\Database\Seeder;

class DomainRelationsSeeder extends Seeder
{
    public function run(): void
    {
        $serieD = Serie::where('code', 'D')->first();
        $serieC = Serie::where('code', 'C')->first();
        $serieA = Serie::where('code', 'A')->first();
        $serieG2 = Serie::where('code', 'G2')->first();

        $fieldSante = Field::where('name', 'Médecine et Santé')->first();
        $fieldInfo = Field::where('name', 'Génie Logiciel et Informatique')->first();
        $fieldDroit = Field::where('name', 'Droit et Sciences Juridiques')->first();
        $fieldAgro = Field::where('name', 'Agronomie et Agro-industrie')->first();
        $fieldCompta = Field::where('name', 'Comptabilité et Audit')->first();

        $ul = Establishment::where('name', 'Université de Lomé (UL)')->first();
        $uk = Establishment::where('name', 'Université de Kara (UK)')->first();

        $sectorTech = GrowthSector::where('name', 'Technologie et Numérique')->first();
        $sectorAgro = GrowthSector::where('name', 'Agrobusiness')->first();

        // Relations Field <-> Serie
        if ($fieldSante) {
            $fieldSante->series()->sync([$serieD->id, $serieC->id]);
        }
        if ($fieldInfo) {
            $fieldInfo->series()->sync([$serieC->id, $serieD->id]);
        }
        if ($fieldDroit) {
            $fieldDroit->series()->sync([$serieA->id, $serieD->id]);
        }
        if ($fieldAgro) {
            $fieldAgro->series()->sync([$serieD->id, $serieC->id]);
        }
        if ($fieldCompta) {
            $fieldCompta->series()->sync([$serieG2->id, $serieD->id]);
        }

        // Relations Establishment <-> Field
        if ($ul) {
            $ul->fields()->sync(Field::pluck('id')->toArray());
        }
        if ($uk) {
            $uk->fields()->sync(Field::pluck('id')->toArray());
        }

        // Relations GrowthSector <-> Field
        if ($sectorTech && $fieldInfo) {
            $sectorTech->fields()->sync([$fieldInfo->id]);
        }
        // Relations Field <-> Career
        if ($fieldInfo) {
            $dev = Career::where('name', 'Développeur Fullstack')->first();
            if ($dev) {
                $fieldInfo->careers()->sync([$dev->id]);
            }
        }

        // Relations Course <-> Career
        $courseGL = Course::where('name', 'Licence Génie Logiciel')->first();
        $dev = Career::where('name', 'Développeur Fullstack')->first();
        if ($courseGL && $dev) {
            $courseGL->careers()->sync([$dev->id]);
        }
    }
}

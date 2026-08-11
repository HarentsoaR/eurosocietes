<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

/**
 * Canonical fiche blocks. `ordre` is a spacing value so new blocks can be
 * inserted between existing ones; `visible` is the global default visibility
 * before any per-entity override exists in section_reorders.
 */
class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['code' => 'fiche_informations', 'libelle' => 'Informations', 'ordre' => 10],
            ['code' => 'fiche_siege', 'libelle' => 'Siège & contacts', 'ordre' => 20],
            ['code' => 'fiche_activite', 'libelle' => 'Activité & NAF', 'ordre' => 30],
            ['code' => 'fiche_geolocalisation', 'libelle' => 'Géolocalisation', 'ordre' => 40],
            ['code' => 'fiche_specialites', 'libelle' => 'Spécialités', 'ordre' => 50],
            ['code' => 'fiche_etablissements', 'libelle' => 'Établissements', 'ordre' => 60],
            ['code' => 'fiche_dirigeants', 'libelle' => 'Dirigeants', 'ordre' => 70],
            ['code' => 'fiche_documents', 'libelle' => 'Documents', 'ordre' => 80],
            ['code' => 'fiche_faq', 'libelle' => 'Questions fréquentes', 'ordre' => 90],
            ['code' => 'fiche_ia', 'libelle' => 'Contenu IA', 'ordre' => 100],
            ['code' => 'fiche_publicites', 'libelle' => 'Publicités', 'ordre' => 110],
        ];

        foreach ($sections as $section) {
            Section::updateOrCreate(
                ['code' => $section['code']],
                [...$section, 'type' => 'entreprise'],
            );
        }
    }
}

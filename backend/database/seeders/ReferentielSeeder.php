<?php

namespace Database\Seeders;

use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Specialite;
use App\Models\Ville;
use App\Models\VilleCodePostal;
use Illuminate\Database\Seeder;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPays();
        $this->seedRegions();
        $this->seedDepartements();
        $this->seedVilles();
        $this->seedActivitesNaf();
        $this->seedSpecialites();
    }

    private function seedPays(): void
    {
        Pays::updateOrCreate(
            ['code_iso2' => 'FR'],
            ['code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']
        );
    }

    private function seedRegions(): void
    {
        $pays = Pays::where('code_iso2', 'FR')->first();

        $regions = [
            ['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes'],
            ['code' => '27', 'libelle' => 'Bourgogne-Franche-Comté', 'slug' => 'bourgogne-franche-comte'],
            ['code' => '53', 'libelle' => 'Bretagne', 'slug' => 'bretagne'],
            ['code' => '24', 'libelle' => 'Centre-Val de Loire', 'slug' => 'centre-val-de-loire'],
            ['code' => '94', 'libelle' => 'Corse', 'slug' => 'corse'],
            ['code' => '44', 'libelle' => 'Grand Est', 'slug' => 'grand-est'],
            ['code' => '32', 'libelle' => 'Hauts-de-France', 'slug' => 'hauts-de-france'],
            ['code' => '11', 'libelle' => 'Île-de-France', 'slug' => 'ile-de-france'],
            ['code' => '28', 'libelle' => 'Normandie', 'slug' => 'normandie'],
            ['code' => '75', 'libelle' => 'Nouvelle-Aquitaine', 'slug' => 'nouvelle-aquitaine'],
            ['code' => '76', 'libelle' => 'Occitanie', 'slug' => 'occitanie'],
            ['code' => '52', 'libelle' => 'Pays de la Loire', 'slug' => 'pays-de-la-loire'],
            ['code' => '93', 'libelle' => "Provence-Alpes-Côte d'Azur", 'slug' => 'provence-alpes-cote-dazur'],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(
                ['code' => $region['code']],
                [...$region, 'pays_id' => $pays->id]
            );
        }
    }

    private function seedDepartements(): void
    {
        $regionAra = Region::where('code', '84')->first();
        $regionIdf = Region::where('code', '11')->first();
        $regionPaca = Region::where('code', '93')->first();

        $departements = [
            ['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $regionAra->id],
            ['code' => '38', 'libelle' => 'Isère', 'slug' => 'isere', 'region_id' => $regionAra->id],
            ['code' => '75', 'libelle' => 'Paris', 'slug' => 'paris', 'region_id' => $regionIdf->id],
            ['code' => '92', 'libelle' => 'Hauts-de-Seine', 'slug' => 'hauts-de-seine', 'region_id' => $regionIdf->id],
            ['code' => '13', 'libelle' => 'Bouches-du-Rhône', 'slug' => 'bouches-du-rhone', 'region_id' => $regionPaca->id],
        ];

        foreach ($departements as $dep) {
            Departement::updateOrCreate(
                ['code' => $dep['code']],
                $dep
            );
        }
    }

    private function seedVilles(): void
    {
        $dep69 = Departement::where('code', '69')->first();
        $dep75 = Departement::where('code', '75')->first();
        $dep13 = Departement::where('code', '13')->first();

        $villes = [
            ['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $dep69->id, 'population' => 522969],
            ['code_insee' => '69381', 'libelle' => 'Villeurbanne', 'slug' => 'villeurbanne', 'departement_id' => $dep69->id, 'population' => 150644],
            ['code_insee' => '75056', 'libelle' => 'Paris', 'slug' => 'paris', 'departement_id' => $dep75->id, 'population' => 2133111],
            ['code_insee' => '13055', 'libelle' => 'Marseille', 'slug' => 'marseille', 'departement_id' => $dep13->id, 'population' => 873076],
        ];

        foreach ($villes as $ville) {
            Ville::updateOrCreate(
                ['code_insee' => $ville['code_insee']],
                $ville
            );
        }

        $villeLyon = Ville::where('code_insee', '69123')->first();
        VilleCodePostal::updateOrCreate(
            ['ville_id' => $villeLyon->id, 'code_postal' => '69001'],
            []
        );
        VilleCodePostal::updateOrCreate(
            ['ville_id' => $villeLyon->id, 'code_postal' => '69003'],
            []
        );
    }

    private function seedActivitesNaf(): void
    {
        $activites = [
            ['code' => '56.10A', 'section' => 'I', 'section_libelle' => 'Hébergement et restauration', 'libelle' => 'Restauration traditionnelle'],
            ['code' => '56.10B', 'section' => 'I', 'section_libelle' => 'Hébergement et restauration', 'libelle' => 'Cafétérias et autres libres-services'],
            ['code' => '56.21Z', 'section' => 'I', 'section_libelle' => 'Hébergement et restauration', 'libelle' => 'Services des traiteurs'],
            ['code' => '47.11A', 'section' => 'G', 'section_libelle' => 'Commerce de détail', 'libelle' => 'Commerce de détail sur éventails non spécialisé'],
            ['code' => '47.11B', 'section' => 'G', 'section_libelle' => 'Commerce de détail', 'libelle' => 'Commerce de détail à prédominance alimentaire'],
            ['code' => '47.20Z', 'section' => 'G', 'section_libelle' => 'Commerce de détail', 'libelle' => "Commerce de détail d'alimentation en magasin spécialisé"],
            ['code' => '41.20A', 'section' => 'F', 'section_libelle' => 'Construction', 'libelle' => 'Construction de maisons'],
            ['code' => '43.31Z', 'section' => 'F', 'section_libelle' => 'Construction', 'libelle' => 'Travaux de plâtrerie'],
            ['code' => '43.32A', 'section' => 'F', 'section_libelle' => 'Construction', 'libelle' => 'Travaux de menuiserie'],
            ['code' => '45.11Z', 'section' => 'G', 'section_libelle' => 'Commerce de détail', 'libelle' => 'Commerce de détail de véhicules automobiles'],
            ['code' => '45.20A', 'section' => 'G', 'section_libelle' => 'Commerce de détail', 'libelle' => 'Entretien et réparation de véhicules automobiles'],
            ['code' => '62.01Z', 'section' => 'J', 'section_libelle' => 'Information et communication', 'libelle' => 'Programmation informatique'],
            ['code' => '62.02A', 'section' => 'J', 'section_libelle' => 'Information et communication', 'libelle' => 'Conseil en systèmes et logiciels informatiques'],
            ['code' => '62.09Z', 'section' => 'J', 'section_libelle' => 'Information et communication', 'libelle' => 'Autres activités informatiques'],
            ['code' => '69.10Z', 'section' => 'M', 'section_libelle' => 'Activités spécialisées', 'libelle' => 'Activités juridiques'],
            ['code' => '69.20Z', 'section' => 'M', 'section_libelle' => 'Activités spécialisées', 'libelle' => 'Activités comptables'],
            ['code' => '70.21Z', 'section' => 'M', 'section_libelle' => 'Activités spécialisées', 'libelle' => 'Relations publiques et communication'],
            ['code' => '70.22Z', 'section' => 'M', 'section_libelle' => 'Activités spécialisées', 'libelle' => 'Conseil de gestion'],
            ['code' => '71.12A', 'section' => 'M', 'section_libelle' => 'Activités spécialisées', 'libelle' => 'Ingénierie'],
            ['code' => '86.10A', 'section' => 'Q', 'section_libelle' => 'Santé humaine', 'libelle' => 'Activités de médecine générale'],
            ['code' => '86.21A', 'section' => 'Q', 'section_libelle' => 'Santé humaine', 'libelle' => 'Activités de médecine dentaire'],
            ['code' => '86.22B', 'section' => 'Q', 'section_libelle' => 'Santé humaine', 'libelle' => 'Activités de kinésithérapie'],
            ['code' => '86.90A', 'section' => 'Q', 'section_libelle' => 'Santé humaine', 'libelle' => 'Activités des infirmiers'],
            ['code' => '96.02A', 'section' => 'S', 'section_libelle' => 'Activités ménagères', 'libelle' => 'Coiffure et soins esthétiques'],
            ['code' => '96.04X', 'section' => 'S', 'section_libelle' => 'Activités ménagères', 'libelle' => 'Entretien corporel'],
        ];

        foreach ($activites as $activite) {
            ActiviteNaf::updateOrCreate(
                ['code' => $activite['code']],
                $activite
            );
        }
    }

    private function seedSpecialites(): void
    {
        $specialites = [
            ['libelle' => 'Boulangerie', 'slug' => 'boulangerie'],
            ['libelle' => 'Pâtisserie', 'slug' => 'patisserie'],
            ['libelle' => 'Charcuterie', 'slug' => 'charcuterie'],
            ['libelle' => 'Boucherie', 'slug' => 'boucherie'],
            ['libelle' => 'Fromagerie', 'slug' => 'fromagerie'],
            ['libelle' => 'Cuisine française', 'slug' => 'cuisine-francaise'],
            ['libelle' => 'Cuisine italienne', 'slug' => 'cuisine-italienne'],
            ['libelle' => 'Cuisine japonaise', 'slug' => 'cuisine-japonaise'],
            ['libelle' => 'Plomberie', 'slug' => 'plomberie'],
            ['libelle' => 'Électricité', 'slug' => 'electricite'],
            ['libelle' => 'Menuiserie', 'slug' => 'menuiserie'],
            ['libelle' => 'Peinture', 'slug' => 'peinture'],
            ['libelle' => 'Informatique', 'slug' => 'informatique'],
            ['libelle' => 'Comptabilité', 'slug' => 'comptabilite'],
            ['libelle' => 'Avocat', 'slug' => 'avocat'],
            ['libelle' => 'Médecine générale', 'slug' => 'medecine-generale'],
            ['libelle' => 'Kinésithérapie', 'slug' => 'kinesitherapie'],
            ['libelle' => 'Coiffure', 'slug' => 'coiffure'],
            ['libelle' => 'Esthétique', 'slug' => 'esthetique'],
            ['libelle' => 'Immobilier', 'slug' => 'immobilier'],
        ];

        foreach ($specialites as $specialite) {
            Specialite::updateOrCreate(
                ['slug' => $specialite['slug']],
                $specialite
            );
        }
    }
}

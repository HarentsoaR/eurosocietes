<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Historique;
use App\Models\Import;
use App\Models\Recherche;
use App\Models\Statistique;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_historique_polymorphique(): void
    {
        $entreprise = Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);

        $historique = Historique::create([
            'entity_type' => Entreprise::class,
            'entity_id' => $entreprise->id,
            'action' => 'update',
            'avant' => ['visible' => false],
            'apres' => ['visible' => true],
        ]);

        $this->assertInstanceOf(Entreprise::class, $historique->entitable);
        $this->assertSame(['visible' => true], $historique->apres);
    }

    public function test_import_avec_compteurs_et_logs(): void
    {
        $import = Import::create([
            'type' => 'sirene_unites',
            'statut' => 'processing',
            'lignes_total' => 100,
            'lignes_inserees' => 0,
            'resume_state' => ['dernier_offset' => 0],
        ]);

        $import->logs()->create(['niveau' => 'info', 'message' => 'Démarrage']);
        $import->logs()->create(['niveau' => 'error', 'message' => 'SIREN invalide', 'siren' => '356000000']);

        $this->assertCount(2, $import->logs);
        $this->assertSame(['dernier_offset' => 0], $import->resume_state);
    }

    public function test_statistique_unique_par_periode(): void
    {
        Statistique::create(['type' => 'vue_entreprise', 'entity_type' => Entreprise::class, 'entity_id' => 1, 'periode' => '2026-08-01', 'compteur' => 5]);

        $this->expectException(QueryException::class);
        Statistique::create(['type' => 'vue_entreprise', 'entity_type' => Entreprise::class, 'entity_id' => 1, 'periode' => '2026-08-01', 'compteur' => 6]);
    }

    public function test_recherche_log(): void
    {
        $recherche = Recherche::create(['terme' => 'plombier lyon', 'nb_resultats' => 12]);

        $this->assertDatabaseHas('recherches', ['id' => $recherche->id]);
    }
}

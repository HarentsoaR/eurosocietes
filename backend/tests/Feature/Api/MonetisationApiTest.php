<?php

namespace Tests\Feature\Api;

use App\Models\Abonnement;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Passeport;
use App\Models\Publicite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class MonetisationApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function entreprise(): Entreprise
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);

        return Entreprise::create([
            'siren' => '356000013',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => true,
        ]);
    }

    public function test_public_can_view_a_published_passeport(): void
    {
        $entreprise = $this->entreprise();
        Passeport::create(['entreprise_id' => $entreprise->id, 'statut' => 'valide', 'score_confidence' => 85, 'badges' => ['complet'], 'is_validated' => true]);

        $this->getJson("/api/v1/entreprises/{$entreprise->siren}/passeport")
            ->assertOk()
            ->assertJsonPath('data.statut', 'valide');
    }

    public function test_public_cannot_view_an_unvalidated_passeport(): void
    {
        $entreprise = $this->entreprise();
        Passeport::create(['entreprise_id' => $entreprise->id, 'statut' => 'soumis', 'score_confidence' => 50, 'badges' => [], 'is_validated' => false]);

        $this->getJson("/api/v1/entreprises/{$entreprise->siren}/passeport")
            ->assertStatus(404);
    }

    public function test_public_can_list_active_publicites(): void
    {
        $entreprise = $this->entreprise();
        Publicite::create(['titre' => 'Crédit agricole', 'emplacement' => 'header', 'statut' => 'active', 'entreprise_id' => $entreprise->id]);
        Publicite::create(['titre' => 'Brouillon', 'emplacement' => 'header', 'statut' => 'brouillon']);

        $this->getJson('/api/v1/publicites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titre', 'Crédit agricole');
    }

    public function test_public_can_list_subscriptions_of_an_entreprise(): void
    {
        $entreprise = $this->entreprise();
        Abonnement::create(['entreprise_id' => $entreprise->id, 'plan' => 'essentiel', 'statut' => 'actif', 'date_debut' => now()->toDateString()]);

        $this->getJson("/api/v1/entreprises/{$entreprise->siren}/abonnement")
            ->assertOk()
            ->assertJsonPath('data.plan', 'essentiel');
    }
}

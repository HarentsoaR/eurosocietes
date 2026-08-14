<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\Publicite;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetisationTest extends TestCase
{
    use RefreshDatabase;

    private function entreprise(): Entreprise
    {
        return Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);
    }

    public function test_abonnement_lie_a_l_entreprise(): void
    {
        $entreprise = $this->entreprise();

        $abonnement = Abonnement::create([
            'entreprise_id' => $entreprise->id,
            'plan' => 'premium',
            'statut' => 'actif',
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addYear()->toDateString(),
        ]);

        $this->assertTrue($entreprise->abonnements->contains($abonnement));
        $this->assertSame('premium', $abonnement->plan);
    }

    public function test_abonnement_dates_incoherentes_rejete(): void
    {
        $this->expectException(QueryException::class);

        Abonnement::create([
            'entreprise_id' => $this->entreprise()->id,
            'plan' => 'premium',
            'statut' => 'actif',
            'date_debut' => now()->addMonth()->toDateString(),
            'date_fin' => now()->toDateString(),
        ]);
    }

    public function test_publicite_liee_a_l_entreprise(): void
    {
        $entreprise = $this->entreprise();

        $publicite = Publicite::create([
            'entreprise_id' => $entreprise->id,
            'titre' => 'Bannière',
            'emplacement' => 'fiche_entreprise',
            'statut' => 'publie',
        ]);

        $this->assertTrue($entreprise->publicites->contains($publicite));
    }
}

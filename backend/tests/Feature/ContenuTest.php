<?php

namespace Tests\Feature;

use App\Models\ContenuIa;
use App\Models\Document;
use App\Models\Entreprise;
use App\Models\Faq;
use App\Models\Passeport;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContenuTest extends TestCase
{
    use RefreshDatabase;

    private function entreprise(): Entreprise
    {
        return Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);
    }

    public function test_faq_et_contenu_ia_polymorphiques(): void
    {
        $entreprise = $this->entreprise();

        $entreprise->faqs()->create(['question' => 'Quels horaires ?', 'reponse' => '9h-19h', 'ordre' => 1, 'visible' => true]);
        $entreprise->contenusIa()->create(['type_contenu' => 'presentation', 'contenu' => 'Présentation IA', 'statut' => 'done']);

        $this->assertCount(1, $entreprise->faqs);
        $this->assertCount(1, $entreprise->contenusIa);
        $this->assertInstanceOf(Faq::class, $entreprise->faqs->first());
        $this->assertInstanceOf(ContenuIa::class, $entreprise->contenusIa->first());
    }

    public function test_document_polymorphique(): void
    {
        $entreprise = $this->entreprise();

        $document = Document::create([
            'entity_type' => Entreprise::class,
            'entity_id' => $entreprise->id,
            'type' => 'kbis',
            'titre' => 'Kbis',
            'chemin' => 'entreprises/1/documents/kbis.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertInstanceOf(Entreprise::class, $document->entitable);
    }

    public function test_passeport_1_a_1_avec_badges(): void
    {
        $entreprise = $this->entreprise();

        $passeport = Passeport::create([
            'entreprise_id' => $entreprise->id,
            'statut' => 'valide',
            'score_confidence' => 85,
            'badges' => ['coordonnees_validees', 'avis_verifies'],
        ]);

        $this->assertTrue($entreprise->passeport->is($passeport));
        $this->assertSame(['coordonnees_validees', 'avis_verifies'], $passeport->badges);

        $this->expectException(QueryException::class);
        Passeport::create(['entreprise_id' => $entreprise->id, 'statut' => 'non_soumis', 'badges' => []]);
    }
}

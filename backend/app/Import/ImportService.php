<?php

namespace App\Import;

use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Historique;
use App\Models\Import;
use App\Models\ImportLog;
use App\Models\Ville;
use Illuminate\Support\Facades\DB;

class ImportService
{
    private QualityChecker $qualite;

    private SireneImporter $sirene;

    public function __construct(?QualityChecker $qualite = null, ?SireneImporter $sirene = null)
    {
        $this->qualite = $qualite ?? new QualityChecker();
        $this->sirene = $sirene ?? new SireneImporter();
    }

    /**
     * Importe un lot de lignes unités légales.
     *
     * @param  array<int, array<string, string>>  $lignes
     * @return array{inserees: int, maj: int, radiees: int, erreurs: int}
     */
    public function importerUnites(array $lignes, Import $import): array
    {
        $stats = ['inserees' => 0, 'maj' => 0, 'radiees' => 0, 'erreurs' => 0];

        $nafIds = ActiviteNaf::pluck('id', 'code')->all();
        $villeIds = Ville::pluck('id', 'code_insee')->all();

        $upsert = [];
        foreach ($lignes as $numero => $ligne) {
            $mappe = $this->sirene->mappingUnite($ligne);
            if ($mappe === null) {
                $stats['erreurs']++;
                $this->journaliser($import, 'error', 'SIREN invalide', $ligne['siren'] ?? '', null, $numero);

                continue;
            }

            if ($this->qualite->estRadiee($mappe['etat_administratif'])) {
                $this->radierUnite($mappe['siren'], $mappe, $import);
                $stats['radiees']++;

                continue;
            }

            $nafId = $mappe['activite_naf'] !== null ? ($nafIds[$mappe['activite_naf']] ?? null) : null;
            $villeId = $mappe['code_insee'] !== null ? ($villeIds[str_pad($mappe['code_insee'], 5, '0', STR_PAD_LEFT)] ?? null) : null;

            $upsert[] = [
                'siren' => $mappe['siren'],
                'denomination' => $mappe['denomination'],
                'nom' => $mappe['nom'],
                'prenoms' => $mappe['prenoms'],
                'sigle' => $mappe['sigle'],
                'categorie_juridique' => $mappe['categorie_juridique'],
                'categorie_entreprise' => $mappe['categorie_entreprise'],
                'tranche_effectifs' => $mappe['tranche_effectifs'],
                'annee_effectifs' => $mappe['annee_effectifs'],
                'caractere_employeur' => $mappe['caractere_employeur'],
                'etat_administratif' => 'A',
                'date_creation' => $mappe['date_creation'],
                'date_debut_activite' => $mappe['date_debut_activite'],
                'activite_naf_id' => $nafId,
                'ville_id' => $villeId,
                'adresse_complete' => $mappe['adresse_complete'],
                'visible' => true,
                'updated_at' => now(),
            ];
        }

        if ($upsert !== []) {
            [$inserees, $maj] = $this->upsertEntreprises($upsert);
            $stats['inserees'] = $inserees;
            $stats['maj'] = $maj;
        }

        return $stats;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lignes
     * @return array{0: int, 1: int} [inserees, maj]
     */
    private function upsertEntreprises(array $lignes): array
    {
        $sirens = array_column($lignes, 'siren');
        $existants = Entreprise::withTrashed()->whereIn('siren', $sirens)->pluck('id', 'siren')->all();

        Entreprise::query()->upsert(
            $lignes,
            ['siren'],
            ['denomination', 'nom', 'prenoms', 'sigle', 'categorie_juridique', 'categorie_entreprise',
                'tranche_effectifs', 'annee_effectifs', 'caractere_employeur', 'etat_administratif',
                'date_creation', 'date_debut_activite', 'activite_naf_id', 'ville_id',
                'adresse_complete', 'visible', 'updated_at', 'deleted_at',
            ]
        );

        $inserees = 0;
        foreach ($sirens as $siren) {
            if (! isset($existants[$siren])) {
                $inserees++;
            }
        }

        return [$inserees, count($sirens) - $inserees];
    }

    /**
     * @param  array<string, mixed>  $mappe
     */
    private function radierUnite(string $siren, array $mappe, Import $import): void
    {
        $entreprise = Entreprise::withTrashed()->where('siren', $siren)->first();

        if ($entreprise === null) {
            $entreprise = Entreprise::create([
                'siren' => $siren,
                'denomination' => $mappe['denomination'],
                'nom' => $mappe['nom'],
                'prenoms' => $mappe['prenoms'],
                'etat_administratif' => 'C',
                'date_radiation' => now()->toDateString(),
                'visible' => false,
            ]);
        }

        if (! $entreprise->trashed()) {
            $entreprise->forceFill(['etat_administratif' => 'C', 'date_radiation' => now()->toDateString()])->save();
            $entreprise->delete();

            Historique::create([
                'entity_type' => Entreprise::class,
                'entity_id' => $entreprise->id,
                'action' => 'radiation',
                'apres' => ['etat_administratif' => 'C'],
                'import_id' => $import->id,
                'created_at' => now(),
            ]);
        }
    }

    private function journaliser(Import $import, string $niveau, string $message, ?string $siren, ?string $siret, ?int $ligne): void
    {
        ImportLog::create([
            'import_id' => $import->id,
            'niveau' => $niveau,
            'message' => $message,
            'siren' => $siren,
            'siret' => $siret,
            'ligne' => $ligne,
            'created_at' => now(),
        ]);
    }
}

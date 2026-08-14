<?php

namespace App\Import;

class EtablissementImporter
{
    public function __construct(private QualityChecker $qualite = new QualityChecker) {}

    /**
     * @param  array<string, string>  $ligne
     * @return array<string, mixed>|null
     */
    public function mappingEtablissement(array $ligne): ?array
    {
        $siret = trim($ligne['siret'] ?? '');
        if (! $this->qualite->validerSiret($siret)) {
            return null;
        }

        return [
            'siren' => trim($ligne['siren'] ?? ''),
            'siret' => $siret,
            'nic' => substr($siret, -5),
            'slug' => Slugger::faire($this->baseSlug($ligne), $siret),
            'est_siege' => strtolower(trim($ligne['etablissementSiege'] ?? '')) === 'true',
            'etat_administratif' => trim($ligne['etatAdministratifEtablissement'] ?? 'A'),
            'activite_naf' => $this->nullSiVide($ligne['activitePrincipaleEtablissement'] ?? ''),
            'enseigne' => $this->nullSiVide($ligne['enseigne1Etablissement'] ?? ''),
            'numero_voie' => $this->nullSiVide($ligne['numeroVoieEtablissement'] ?? ''),
            'type_voie' => $this->nullSiVide($ligne['typeVoieEtablissement'] ?? ''),
            'libelle_voie' => $this->nullSiVide($ligne['libelleVoieEtablissement'] ?? ''),
            'complement_adresse' => $this->nullSiVide($ligne['complementAdresseEtablissement'] ?? ''),
            'code_postal' => $this->nullSiVide($ligne['codePostalEtablissement'] ?? ''),
            'libelle_commune' => $this->nullSiVide($ligne['libelleCommuneEtablissement'] ?? ''),
            'code_insee' => $this->nullSiVide($ligne['codeCommuneEtablissement'] ?? ''),
        ];
    }

    private function baseSlug(array $ligne): string
    {
        $enseigne = trim($ligne['enseigne1Etablissement'] ?? '');

        return $enseigne !== '' ? $enseigne : trim($ligne['siret'] ?? '');
    }

    private function nullSiVide(string $valeur): ?string
    {
        $valeur = trim($valeur);

        return $valeur === '' ? null : $valeur;
    }
}

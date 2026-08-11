<?php

namespace App\Import;

class SireneImporter
{
    public function __construct(private QualityChecker $qualite = new QualityChecker())
    {
    }

    /**
     * Mappe une ligne INSEE (unité légale) vers les colonnes entreprises.
     * Retourne null si le SIREN est invalide.
     *
     * @param  array<string, string>  $ligne
     * @return array<string, mixed>|null
     */
    public function mappingUnite(array $ligne): ?array
    {
        $siren = trim($ligne['siren'] ?? '');
        if (! $this->qualite->validerSiren($siren)) {
            return null;
        }

        $dateCreation = $this->dateValide($ligne['dateCreationUniteLegale'] ?? '');
        $dateDebut = $this->dateValide($ligne['dateDebutUniteLegale'] ?? '');

        return [
            'siren' => $siren,
            'slug' => Slugger::faire($this->baseSlug($ligne), $siren),
            'denomination' => $this->nullSiVide($ligne['denominationUniteLegale'] ?? ''),
            'nom' => $this->nullSiVide($ligne['nomUniteLegale'] ?? ''),
            'prenoms' => $this->nullSiVide($ligne['prenom1UniteLegale'] ?? ''),
            'sigle' => $this->nullSiVide($ligne['sigleUniteLegale'] ?? ''),
            'categorie_juridique' => $this->nullSiVide($ligne['categorieJuridiqueUniteLegale'] ?? ''),
            'categorie_entreprise' => $this->nullSiVide($ligne['categorieEntreprise'] ?? ''),
            'tranche_effectifs' => $this->nullSiVide($ligne['trancheEffectifsUniteLegale'] ?? ''),
            'annee_effectifs' => $this->intOuNull($ligne['anneeEffectifsUniteLegale'] ?? ''),
            'caractere_employeur' => $this->ouiOuNull($ligne['caractereEmployeurUniteLegale'] ?? ''),
            'etat_administratif' => trim($ligne['etatAdministratifUniteLegale'] ?? 'A'),
            'date_creation' => $dateCreation,
            'date_debut_activite' => $dateDebut,
            'activite_naf' => $this->nullSiVide($ligne['activitePrincipaleUniteLegale'] ?? ''),
            'code_postal' => $this->nullSiVide($ligne['codePostalUniteLegale'] ?? ''),
            'libelle_commune' => $this->nullSiVide($ligne['libelleCommuneUniteLegale'] ?? ''),
            'code_insee' => $this->nullSiVide($ligne['codeCommuneUniteLegale'] ?? ''),
            'adresse_complete' => $this->adresseComplete($ligne),
        ];
    }

    private function baseSlug(array $ligne): string
    {
        $denomination = trim($ligne['denominationUniteLegale'] ?? '');
        if ($denomination !== '') {
            return $denomination;
        }

        return trim(($ligne['nomUniteLegale'] ?? '').' '.($ligne['prenom1UniteLegale'] ?? ''));
    }

    private function adresseComplete(array $ligne): ?string
    {
        $numero = trim($ligne['numeroVoieUniteLegale'] ?? '');
        $type = trim($ligne['typeVoieUniteLegale'] ?? '');
        $libelle = trim($ligne['libelleVoieUniteLegale'] ?? '');
        $complement = trim($ligne['complementAdresseUniteLegale'] ?? '');
        $cp = trim($ligne['codePostalUniteLegale'] ?? '');
        $commune = trim($ligne['libelleCommuneUniteLegale'] ?? '');

        $parts = array_filter([$complement, trim("$numero $type $libelle"), $cp, $commune]);

        return $parts !== [] ? implode(', ', $parts) : null;
    }

    private function dateValide(string $valeur): ?string
    {
        $valeur = trim($valeur);
        if ($valeur === '') {
            return null;
        }
        $date = \DateTime::createFromFormat('Y-m-d', $valeur);

        return $date !== false ? $date->format('Y-m-d') : null;
    }

    private function nullSiVide(string $valeur): ?string
    {
        $valeur = trim($valeur);

        return $valeur === '' ? null : $valeur;
    }

    private function intOuNull(string $valeur): ?int
    {
        $valeur = trim($valeur);
        if ($valeur === '' || ! ctype_digit($valeur)) {
            return null;
        }

        return (int) $valeur;
    }

    private function ouiOuNull(string $valeur): ?bool
    {
        return strtoupper(trim($valeur)) === 'O';
    }
}

<?php

namespace App\Import;

class QualityChecker
{
    public function validerSiren(string $siren): bool
    {
        if (! preg_match('/^\d{9}$/', $siren)) {
            return false;
        }

        return $this->luhn($siren);
    }

    public function validerSiret(string $siret): bool
    {
        if (! preg_match('/^\d{14}$/', $siret)) {
            return false;
        }

        return $this->luhn($siret);
    }

    public function estRadiee(string $etat): bool
    {
        return $etat !== 'A';
    }

    private function luhn(string $nombre): bool
    {
        $somme = 0;
        $inverse = strrev($nombre);

        for ($i = 0; $i < strlen($inverse); $i++) {
            $chiffre = (int) $inverse[$i];
            if ($i % 2 === 1) {
                $chiffre *= 2;
                if ($chiffre > 9) {
                    $chiffre -= 9;
                }
            }
            $somme += $chiffre;
        }

        return $somme % 10 === 0;
    }
}

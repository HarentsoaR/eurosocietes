<?php

namespace App\Import;

use Generator;
use League\Csv\Reader;

class CsvReader
{
    /**
     * Lit un fichier CSV par lots de lignes associatives.
     *
     * @return Generator<int, array<int, array<string, string>>>
     */
    public function lireLots(string $chemin, int $tailleLot = 2000): Generator
    {
        $reader = Reader::from($chemin);
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        $lot = [];
        foreach ($reader->getRecords() as $ligne) {
            $lot[] = $ligne;
            if (count($lot) >= $tailleLot) {
                yield $lot;
                $lot = [];
            }
        }

        if ($lot !== []) {
            yield $lot;
        }
    }
}

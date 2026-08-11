<?php

namespace Tests\Unit\Import;

use App\Import\CsvReader;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    public function test_lit_des_lots_d_assoc_avec_entete(): void
    {
        $chemin = dirname(__DIR__, 2).'/fixtures/unites_minimal.csv';

        $reader = new CsvReader();
        $lots = iterator_to_array($reader->lireLots($chemin, 2));

        $this->assertCount(1, $lots);
        $this->assertSame('356000000', $lots[0][0]['siren']);
        $this->assertSame('Boulangerie Paul', $lots[0][0]['denominationUniteLegale']);
    }

    public function test_decoupe_en_plusieurs_lots(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'csv');
        $contenu = "col1;col2\n";
        for ($i = 1; $i <= 5; $i++) {
            $contenu .= "v{$i};w{$i}\n";
        }
        file_put_contents($tmp, $contenu);

        $reader = new CsvReader();
        $lots = iterator_to_array($reader->lireLots($tmp, 2));

        $this->assertCount(3, $lots); // 2 + 2 + 1

        unlink($tmp);
    }
}

<?php

namespace Tests\Unit\Import;

use App\Import\Downloader;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloaderTest extends TestCase
{
    public function test_telecharge_un_fichier_vers_le_stockage(): void
    {
        Storage::fake('imports');

        Http::fake([
            'insee.example/stock.csv' => Http::response("a;b\n1;2\n"),
        ]);

        $downloader = new Downloader();
        $chemin = $downloader->telecharger('https://insee.example/stock.csv', 'sirene/unites.csv');

        Storage::disk('imports')->assertExists($chemin);
        $this->assertSame("a;b\n1;2\n", Storage::disk('imports')->get($chemin));
    }

    public function test_erreur_reseau_leve_une_exception(): void
    {
        Storage::fake('imports');

        Http::fake(fn () => throw new ConnectionException());

        $downloader = new Downloader();

        $this->expectException(ConnectionException::class);
        $downloader->telecharger('https://insee.example/absent.csv', 'sirene/absent.csv');
    }
}

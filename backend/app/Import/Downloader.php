<?php

namespace App\Import;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Downloader
{
    /**
     * Télécharge une URL vers le disque "imports" (streamé, jamais en mémoire).
     */
    public function telecharger(string $url, string $destination): string
    {
        $reponse = Http::withOptions([
            'stream' => true,
        ])->timeout(3600)->get($url);

        $reponse->throw();

        $flux = $reponse->toPsrResponse()->getBody();
        Storage::disk('imports')->writeStream($destination, $flux->detach() ?? $flux);

        return $destination;
    }
}

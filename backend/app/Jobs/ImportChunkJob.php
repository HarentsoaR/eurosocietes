<?php

namespace App\Jobs;

use App\Import\ImportService;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ImportChunkJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public int $importId,
        public string $type,
        public array $lignes,
    ) {
    }

    public function handle(ImportService $service): void
    {
        $import = Import::findOrFail($this->importId);

        $stats = $this->type === 'unites'
            ? $service->importerUnites($this->lignes, $import)
            : $service->importerEtablissements($this->lignes, $import);

        DB::transaction(function () use ($import, $stats): void {
            $verrou = Import::whereKey($import->id)->lockForUpdate()->firstOrFail();

            $verrou->increment('lignes_traitees', count($this->lignes));
            $verrou->increment('lignes_inserees', $stats['inserees']);
            $verrou->increment('lignes_maj', $stats['maj']);
            $verrou->increment('lignes_radiees', $stats['radiees']);
            $verrou->increment('lignes_erreur', $stats['erreurs']);
            $verrou->update(['resume_state' => ['dernier_offset' => $verrou->lignes_traitees]]);

            if ($verrou->lignes_total !== null && $verrou->lignes_traitees >= $verrou->lignes_total) {
                $verrou->update(['statut' => 'completed', 'completed_at' => now()]);
            }
        });
    }
}

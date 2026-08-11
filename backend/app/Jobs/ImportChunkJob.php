<?php

namespace App\Jobs;

use App\Import\ImportService;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $import->increment('lignes_traitees', count($this->lignes));
        $import->increment('lignes_inserees', $stats['inserees']);
        $import->increment('lignes_maj', $stats['maj']);
        $import->increment('lignes_radiees', $stats['radiees']);
        $import->increment('lignes_erreur', $stats['erreurs']);
        $import->update(['resume_state' => ['dernier_offset' => $import->lignes_traitees]]);

        if ($import->lignes_total !== null && $import->lignes_traitees >= $import->lignes_total) {
            $import->update(['statut' => 'completed', 'completed_at' => now()]);
        }
    }
}

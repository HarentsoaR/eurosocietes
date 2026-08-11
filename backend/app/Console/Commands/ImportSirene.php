<?php

namespace App\Console\Commands;

use App\Import\CsvReader;
use App\Jobs\ImportChunkJob;
use App\Models\Import;
use Illuminate\Console\Command;

class ImportSirene extends Command
{
    protected $signature = 'import:sirene
        {--type=unites : unites|etablissements}
        {--file= : Chemin du fichier CSV INSEE}
        {--diff : Import incrémental (fichier de différences)}
        {--resume : Reprendre l\'import partiel précédent du même type}
        {--taille-lot=2000 : Nombre de lignes par lot}';

    protected $description = 'Importe les données SIRENE (unités légales ou établissements) depuis un CSV.';

    public function handle(CsvReader $reader): int
    {
        $type = $this->option('type');
        if (! in_array($type, ['unites', 'etablissements'], true)) {
            $this->error('--type doit être "unites" ou "etablissements".');

            return self::INVALID;
        }

        $chemin = $this->option('file');
        if (! $chemin || ! is_file($chemin)) {
            $this->error('--file obligatoire et doit pointer vers un CSV existant.');

            return self::INVALID;
        }

        $import = $this->creerImport($type, $chemin);

        $tailleLot = (int) $this->option('taille-lot');

        $total = 0;
        foreach ($reader->lireLots($chemin, $tailleLot) as $lot) {
            $total += count($lot);
        }
        $import->update(['lignes_total' => $total]);

        $dernierOffset = $this->option('resume') ? ($import->resume_state['dernier_offset'] ?? 0) : 0;
        $offset = 0;

        foreach ($reader->lireLots($chemin, $tailleLot) as $lot) {
            if ($offset >= $dernierOffset) {
                ImportChunkJob::dispatch($import->id, $type, $lot);
                $offset += count($lot);

                continue;
            }

            $aGarder = array_slice($lot, $dernierOffset - $offset);
            $offset += count($lot);

            if ($aGarder !== []) {
                ImportChunkJob::dispatch($import->id, $type, $aGarder);
            }
        }

        $this->info("Import planifié : {$import->id} ({$type})");

        return self::SUCCESS;
    }

    private function creerImport(string $type, string $chemin): Import
    {
        $import = Import::where('type', 'sirene_'.$type)
            ->where('statut', 'partial')
            ->latest('id')
            ->first();

        if ($this->option('resume') && $import !== null) {
            return $import;
        }

        return Import::create([
            'type' => 'sirene_'.$type,
            'fichier' => $chemin,
            'statut' => 'processing',
            'started_at' => now(),
        ]);
    }
}

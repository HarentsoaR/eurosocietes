<?php

namespace App\Console\Commands;

use App\Import\CogImporter;
use Illuminate\Console\Command;

class ImportCog extends Command
{
    protected $signature = 'import:cog
        {--communes= : Chemin du CSV des communes (code_insee;libelle;code_postal;code_departement)}
        {--geofla= : Chemin du CSV geofla (code_insee;latitude;longitude)}';

    protected $description = 'Importe le référentiel géographique COG (communes, codes postaux, coordonnées).';

    public function handle(CogImporter $importer): int
    {
        $communes = $this->option('communes');
        if (! $communes || ! is_file($communes)) {
            $this->error('Option --communes obligatoire et doit pointer vers un CSV existant.');

            return self::FAILURE;
        }

        $geofla = $this->option('geofla');
        if ($geofla && ! is_file($geofla)) {
            $this->error('Option --geofla doit pointer vers un CSV existant.');

            return self::FAILURE;
        }

        $stats = $importer->importer($communes, $geofla);

        $this->info(sprintf(
            'Communes importées : %d créées, %d mises à jour.',
            $stats['villes_inserees'],
            $stats['villes_maj']
        ));

        return self::SUCCESS;
    }
}

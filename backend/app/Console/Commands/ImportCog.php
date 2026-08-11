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
        if (! $communes) {
            $this->error('Option --communes obligatoire.');

            return self::FAILURE;
        }

        $stats = $importer->importer($communes, $this->option('geofla'));

        $this->info(sprintf(
            'Communes importées : %d créées, %d mises à jour.',
            $stats['villes_inserees'],
            $stats['villes_maj']
        ));

        return self::SUCCESS;
    }
}

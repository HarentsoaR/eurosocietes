<?php

namespace App\Jobs;

use App\Models\ContenuIa;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Stub for the Phase 5 generation engine: re-mark the content as pending so
 * a worker can (re)generate it. Phase 4 only wires the state transitions.
 */
class RegenerateContenuIa implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ContenuIa $contenu) {}

    public function handle(): void
    {
        $this->contenu->forceFill([
            'statut' => 'pending',
            'generated_at' => null,
        ])->save();
    }
}

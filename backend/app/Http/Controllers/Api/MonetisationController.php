<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\AbonnementResource;
use App\Http\Resources\Api\ApiResourceCollection;
use App\Http\Resources\Api\PasseportResource;
use App\Http\Resources\Api\PubliciteResource;
use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\Passeport;
use App\Models\Publicite;
use App\Support\ApiQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonetisationController extends Controller
{
    public function passeport(Entreprise $entreprise): PasseportResource
    {
        $passeport = Passeport::where('entreprise_id', $entreprise->id)
            ->where('is_validated', true)
            ->firstOrFail();

        return new PasseportResource($passeport);
    }

    public function updatePasseport(Request $request, Passeport $passeport): PasseportResource
    {
        $validated = $request->validate([
            'statut' => ['sometimes', 'string', Rule::in(['non_soumis', 'soumis', 'valide', 'refuse'])],
            'score_confidence' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'badges' => ['sometimes', 'array'],
            'is_validated' => ['sometimes', 'boolean'],
        ]);

        $passeport->update($validated);

        return new PasseportResource($passeport);
    }

    public function abonnement(Entreprise $entreprise): AbonnementResource
    {
        return new AbonnementResource(
            Abonnement::where('entreprise_id', $entreprise->id)->orderByDesc('date_debut')->firstOrFail()
        );
    }

    public function publicites(): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate(Publicite::query()->where('statut', 'active')),
            PubliciteResource::class
        );
    }
}

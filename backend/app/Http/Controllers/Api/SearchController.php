<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SearchRequest;
use App\Http\Resources\Api\ApiResourceCollection;
use App\Http\Resources\Api\EntrepriseResource;
use App\Models\Entreprise;
use App\Models\Recherche;
use App\Support\ApiQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(SearchRequest $request): ApiResourceCollection
    {
        $query = Entreprise::query()
            ->recherche($request->validated('q'))
            ->where('etat_administratif', 'A')
            ->whereRaw('visible = true');

        return new ApiResourceCollection(ApiQuery::paginate($query), EntrepriseResource::class);
    }

    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terme' => ['required', 'string', 'max:255'],
            'nb_resultats' => ['nullable', 'integer', 'min:0'],
        ]);

        $recherche = Recherche::create([
            'terme' => $validated['terme'],
            'nb_resultats' => $validated['nb_resultats'] ?? 0,
            'utilisateur_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => ['id' => $recherche->id]], 201);
    }
}

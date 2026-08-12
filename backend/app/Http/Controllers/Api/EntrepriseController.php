<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreEntrepriseRequest;
use App\Http\Requests\Api\UpdateEntrepriseRequest;
use App\Http\Resources\Api\ApiResourceCollection;
use App\Http\Resources\Api\DirigeantResource;
use App\Http\Resources\Api\EntrepriseResource;
use App\Http\Resources\Api\EtablissementResource;
use App\Http\Resources\Api\SectionResource;
use App\Models\Entreprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    public function index(): ApiResourceCollection
    {
        $query = Entreprise::query()
            ->where('etat_administratif', 'A')
            ->whereRaw('visible = true');

        if ($q = request()->query('q')) {
            $query->recherche($q);
        }

        if ($activiteNafId = request()->query('activite_naf_id')) {
            $query->where('activite_naf_id', $activiteNafId);
        }

        if ($villeId = request()->query('ville_id')) {
            $query->where('ville_id', $villeId);
        }

        return new ApiResourceCollection(
            \App\Support\ApiQuery::paginate($query),
            EntrepriseResource::class
        );
    }

    public function show(Entreprise $entreprise): EntrepriseResource
    {
        $entreprise->load(['etablissements', 'dirigeants']);

        return new EntrepriseResource($entreprise);
    }

    public function store(StoreEntrepriseRequest $request): JsonResponse
    {
        $entreprise = Entreprise::create($request->validated());

        return (new EntrepriseResource($entreprise))->response()->setStatusCode(201);
    }

    public function update(UpdateEntrepriseRequest $request, Entreprise $entreprise): EntrepriseResource
    {
        $entreprise->update($request->validated());

        return new EntrepriseResource($entreprise);
    }

    public function destroy(Entreprise $entreprise): JsonResponse
    {
        $this->authorize('delete', $entreprise);

        $entreprise->delete();

        return response()->json(['data' => ['deleted' => true]], 200);
    }

    public function etablissements(Entreprise $entreprise): ApiResourceCollection
    {
        return new ApiResourceCollection(
            \App\Support\ApiQuery::paginate($entreprise->etablissements()->orderBy('est_siege', 'desc')),
            EtablissementResource::class
        );
    }

    public function dirigeants(Entreprise $entreprise): ApiResourceCollection
    {
        return new ApiResourceCollection(
            \App\Support\ApiQuery::paginate($entreprise->dirigeants()->orderBy('est_principal', 'desc')),
            DirigeantResource::class
        );
    }

    public function sections(Entreprise $entreprise): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => collect($entreprise->sections())->map(function (array $item) => (new SectionResource($item))->resolve())->all(),
        ]);
    }
}
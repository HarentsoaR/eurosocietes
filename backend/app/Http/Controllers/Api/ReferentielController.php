<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\ActiviteNafResource;
use App\Http\Resources\Api\ApiResourceCollection;
use App\Http\Resources\Api\DepartementResource;
use App\Http\Resources\Api\PaysResource;
use App\Http\Resources\Api\RegionResource;
use App\Http\Resources\Api\SpecialiteResource;
use App\Http\Resources\Api\VilleCodePostalResource;
use App\Http\Resources\Api\VilleResource;
use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Specialite;
use App\Models\Ville;
use App\Models\VilleCodePostal;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Builder;

class ReferentielController extends Controller
{
    public function nafActivites(): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate(ActiviteNaf::query()->orderBy('code')),
            ActiviteNafResource::class
        );
    }

    public function nafActivite(ActiviteNaf $nafActivite): ActiviteNafResource
    {
        return new ActiviteNafResource($nafActivite);
    }

    public function specialites(): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate(Specialite::query()->orderBy('libelle')),
            SpecialiteResource::class
        );
    }

    public function pays(): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate(Pays::query()->orderBy('libelle')),
            PaysResource::class
        );
    }

    public function regions(Pays $pays): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate($pays->regions()->orderBy('libelle')),
            RegionResource::class
        );
    }

    public function departements(Region $region): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate($region->departements()->orderBy('libelle')),
            DepartementResource::class
        );
    }

    public function villes(Departement $departement): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate($this->villesQuery(request(), $departement->id)),
            VilleResource::class
        );
    }

    public function ville(Ville $ville): VilleResource
    {
        return new VilleResource($ville);
    }

    public function codePostaux(Ville $ville): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate(VilleCodePostal::where('ville_id', $ville->id)->orderBy('code_postal')),
            VilleCodePostalResource::class
        );
    }

    public function villesSearch(\Illuminate\Http\Request $request): ApiResourceCollection
    {
        return new ApiResourceCollection(
            ApiQuery::paginate($this->villesQuery($request, null)),
            VilleResource::class
        );
    }

    private function villesQuery(\Illuminate\Http\Request $request, ?int $departementId): Builder
    {
        $query = Ville::query()->orderBy('libelle');

        if ($departementId !== null) {
            $query->where('departement_id', $departementId);
        }

        if ($q = $request->query('q')) {
            $query->where('libelle', 'ilike', "%{$q}%");
        }

        return $query;
    }
}

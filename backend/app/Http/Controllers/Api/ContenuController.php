<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\ApiResourceCollection;
use App\Http\Resources\Api\ContenuIaResource;
use App\Http\Resources\Api\DocumentResource;
use App\Http\Resources\Api\FaqResource;
use App\Models\ContenuIa;
use App\Models\Document;
use App\Models\Faq;
use App\Support\ApiQuery;
use App\Support\EntityTypeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContenuController extends Controller
{
    public function contenusIa(): ApiResourceCollection
    {
        $query = ContenuIa::query()->where('statut', 'published');

        if ($entityType = request()->query('entity_type')) {
            $validated = validator(['entity_type' => $entityType], [
                'entity_type' => ['required', Rule::in(array_keys(EntityTypeResolver::allowed()))],
            ])->validate();

            $query->where('entity_type', EntityTypeResolver::resolve($validated['entity_type']))
                ->when(request()->query('entity_id'), fn ($q) => $q->where('entity_id', (int) request()->query('entity_id')));
        }

        return new ApiResourceCollection(ApiQuery::paginate($query), ContenuIaResource::class);
    }

    public function storeContenuIa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', Rule::in(array_keys(EntityTypeResolver::allowed()))],
            'entity_id' => ['required', 'integer'],
            'type_contenu' => ['required', 'string', 'max:50'],
            'contenu' => ['required', 'string'],
            'statut' => ['required', 'string', Rule::in(['draft', 'pending', 'published', 'archived'])],
        ]);

        $contenu = ContenuIa::create([
            ...$validated,
            'entity_type' => EntityTypeResolver::resolve($validated['entity_type']),
        ]);

        return (new ContenuIaResource($contenu))->response()->setStatusCode(201);
    }

    public function updateContenuIa(Request $request, ContenuIa $contenuIa): ContenuIaResource
    {
        $validated = $request->validate([
            'type_contenu' => ['sometimes', 'string', 'max:50'],
            'contenu' => ['sometimes', 'string'],
            'statut' => ['sometimes', 'string', Rule::in(['draft', 'pending', 'published', 'archived'])],
        ]);

        $contenuIa->update($validated);

        return new ContenuIaResource($contenuIa);
    }

    public function faqs(): ApiResourceCollection
    {
        $query = Faq::query()->whereRaw('visible = true');

        $entityType = request()->query('entity_type');
        $entityId = request()->query('entity_id');

        if ($entityType || $entityId) {
            $validated = validator([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ], [
                'entity_type' => ['required', Rule::in(array_keys(EntityTypeResolver::allowed()))],
                'entity_id' => ['required', 'integer'],
            ])->validate();

            $query->where('entity_type', EntityTypeResolver::resolve($validated['entity_type']))
                ->where('entity_id', (int) $validated['entity_id']);
        }

        return new ApiResourceCollection(ApiQuery::paginate($query->orderBy('ordre')), FaqResource::class);
    }

    public function documents(): ApiResourceCollection
    {
        $query = Document::query()->where('statut_validation', 'valide');

        return new ApiResourceCollection(ApiQuery::paginate($query), DocumentResource::class);
    }
}

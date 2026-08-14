<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\StatistiqueResource;
use App\Models\Statistique;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatistiquesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = Statistique::query();

        if ($type = request()->query('type')) {
            $query->where('type', $type);
        }

        if ($periode = request()->query('periode')) {
            $query->where('periode', $periode);
        }

        if ($entityType = request()->query('entity_type')) {
            $query->where('entity_type', $entityType);
        }

        if ($entityId = request()->query('entity_id')) {
            $query->where('entity_id', (int) $entityId);
        }

        return StatistiqueResource::collection($query->orderBy('periode', 'desc')->paginate(100));
    }
}

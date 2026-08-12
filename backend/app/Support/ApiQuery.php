<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApiQuery
{
    public static function paginate(Builder $query, int $default = 15): LengthAwarePaginator
    {
        $perPage = min(max((int) request()->input('per_page', $default), 1), 100);

        return $query->paginate($perPage)->withQueryString();
    }
}

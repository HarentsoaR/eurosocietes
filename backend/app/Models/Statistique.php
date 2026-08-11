<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistique extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'entity_type', 'entity_id', 'periode', 'compteur'];

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'compteur' => 'integer',
        ];
    }
}

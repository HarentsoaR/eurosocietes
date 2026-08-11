<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recherche extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['terme', 'nb_resultats', 'utilisateur_id', 'ip', 'created_at'];

    protected function casts(): array
    {
        return [
            'nb_resultats' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}

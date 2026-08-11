<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passeport extends Model
{
    use HasFactory;

    protected $fillable = ['entreprise_id', 'statut', 'score_confidence', 'badges', 'is_validated', 'validated_at', 'validateur_id', 'commentaire'];

    protected function casts(): array
    {
        return [
            'badges' => 'array',
            'score_confidence' => 'integer',
            'is_validated' => 'boolean',
            'validated_at' => 'datetime',
        ];
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}

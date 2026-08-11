<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publicite extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id', 'utilisateur_id', 'titre', 'description', 'emplacement',
        'url_cible', 'image_path', 'statut', 'date_debut', 'date_fin',
        'budget', 'impressions', 'clics',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'budget' => 'integer',
            'impressions' => 'integer',
            'clics' => 'integer',
        ];
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}

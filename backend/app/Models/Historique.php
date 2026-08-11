<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Historique extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'historique';

    public $timestamps = false;

    protected $fillable = ['entity_type', 'entity_id', 'action', 'avant', 'apres', 'utilisateur_id', 'import_id', 'ip', 'created_at'];

    protected function casts(): array
    {
        return [
            'avant' => 'array',
            'apres' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}

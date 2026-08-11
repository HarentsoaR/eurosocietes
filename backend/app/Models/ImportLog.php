<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['import_id', 'niveau', 'message', 'siren', 'siret', 'ligne', 'context', 'created_at'];

    protected function casts(): array
    {
        return [
            'ligne' => 'integer',
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}

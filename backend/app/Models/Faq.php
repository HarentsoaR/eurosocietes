<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'faq';

    protected $fillable = ['entity_type', 'entity_id', 'question', 'reponse', 'ordre', 'visible'];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'visible' => 'boolean',
        ];
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }
}

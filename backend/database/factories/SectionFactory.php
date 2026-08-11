<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'code' => 'fiche_'.fake()->unique()->slug(2),
            'libelle' => fake()->sentence(2),
            'type' => 'entreprise',
            'ordre' => fake()->numberBetween(1, 200),
            'visible' => true,
        ];
    }
}

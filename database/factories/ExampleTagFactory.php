<?php

declare(strict_types=1);

namespace VmEngine\Example\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VmEngine\Example\Models\ExampleTag;

/**
 * @extends Factory<ExampleTag>
 */
class ExampleTagFactory extends Factory
{
    protected $model = ExampleTag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'color' => $this->faker->hexColor(),
        ];
    }
}

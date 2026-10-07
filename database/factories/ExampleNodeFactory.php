<?php

declare(strict_types=1);

namespace VmEngine\Example\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VmEngine\Example\Models\ExampleNode;

/**
 * @extends Factory<ExampleNode>
 */
class ExampleNodeFactory extends Factory
{
    protected $model = ExampleNode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => ucfirst($this->faker->word().' '.$this->faker->word()),
            'description' => $this->faker->sentence(),
            'position' => 0,
        ];
    }

    public function childOf(ExampleNode $parent): static
    {
        return $this->state(['parent_id' => $parent->id]);
    }
}

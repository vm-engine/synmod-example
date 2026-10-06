<?php

declare(strict_types=1);

namespace VmEngine\Example\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;

/**
 * @extends Factory<ExampleAttachment>
 */
class ExampleAttachmentFactory extends Factory
{
    protected $model = ExampleAttachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'example_id' => Example::factory(),
            'path' => 'examples/attachments/'.$this->faker->uuid().'.jpg',
            'original_name' => $this->faker->word().'.jpg',
            'mime' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1_000, 500_000),
            'position' => 0,
        ];
    }
}

<?php

declare(strict_types=1);

namespace VmEngine\Example\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Models\ExampleTag;

/**
 * @extends Factory<Example>
 */
class ExampleFactory extends Factory
{
    protected $model = Example::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ExampleCategory::factory(),
            'text' => $this->faker->words(rand(1, 4), true),
            'email' => $this->faker->unique()->safeEmail(),
            'protected' => $this->faker->password(8),
            'number' => $this->faker->randomNumber(3),
            'masked' => $this->faker->randomNumber(8),
            'textarea' => $this->faker->paragraph(),
            'dropdown' => $this->faker->randomNumber(1),
            'multidropdown' => $this->faker->randomElements(array_map(function () {
                return rand(0, 9);
            }, array_fill(0, 10, null)), rand(1, 5)),
            'radio' => $this->faker->randomNumber(1),
            'checkbox' => $this->faker->randomElements(array_map(function () {
                return rand(0, 9);
            }, array_fill(0, 10, null)), rand(1, 5)),
            'date' => $this->faker->date(),
            'datetime' => $this->faker->dateTime(),
            'file' => $this->faker->filePath(),
            'color' => $this->faker->hexColor(),
            'status' => $this->faker->randomElement(ExampleStatus::cases()),
            'due_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d') : null,
            'content' => '<p>'.$this->faker->paragraph().'</p>',
            'meta' => ['source' => $this->faker->word(), 'priority' => $this->faker->numberBetween(1, 5)],
            'position' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ExampleStatus::Draft]);
    }

    public function review(): static
    {
        return $this->state(['status' => ExampleStatus::Review]);
    }

    public function published(): static
    {
        return $this->state(['status' => ExampleStatus::Published]);
    }

    public function dueThisMonth(): static
    {
        return $this->state(fn (): array => [
            'due_at' => $this->faker->dateTimeBetween(now()->startOfMonth(), now()->endOfMonth())->format('Y-m-d'),
        ]);
    }

    public function withTags(int $count = 2): static
    {
        return $this->afterCreating(function (Example $example) use ($count): void {
            $example->tags()->attach(ExampleTag::factory()->count($count)->create());
        });
    }
}

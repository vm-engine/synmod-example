<?php

namespace VmEngine\Example\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

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
        ];
    }
}

<?php

declare(strict_types=1);

namespace VmEngine\Example\Seeders;

use Illuminate\Database\Seeder;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Models\ExampleTag;

class ExampleSeeder extends Seeder
{
    private const TOTAL = 60;

    private const DUE_THIS_MONTH = 20;

    /**
     * 60 examples spread evenly over the three statuses, the first 20 due this
     * month (calendar demo), each in an existing category with 1-3 tags.
     */
    public function run(): void
    {
        $categoryIds = ExampleCategory::query()->pluck('id');

        if ($categoryIds->isEmpty()) {
            $categoryIds = ExampleCategory::factory(5)->create()->pluck('id');
        }

        $tagIds = ExampleTag::query()->pluck('id');
        $statuses = ExampleStatus::cases();

        foreach (range(1, self::TOTAL) as $i) {
            $factory = Example::factory()->state([
                'category_id' => $categoryIds->random(),
                'status' => $statuses[$i % count($statuses)],
                'position' => $i,
            ]);

            $example = ($i <= self::DUE_THIS_MONTH ? $factory->dueThisMonth() : $factory)->create();

            if ($tagIds->isNotEmpty()) {
                $example->tags()->attach($tagIds->random(min($tagIds->count(), random_int(1, 3)))->all());
            }
        }
    }
}

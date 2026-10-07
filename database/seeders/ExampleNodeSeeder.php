<?php

declare(strict_types=1);

namespace VmEngine\Example\Seeders;

use Illuminate\Database\Seeder;
use VmEngine\Example\Models\ExampleNode;

class ExampleNodeSeeder extends Seeder
{
    /**
     * 3 roots x 3 children x 2 leaves = 30 nodes. Skips when a tree already exists.
     */
    public function run(): void
    {
        if (ExampleNode::query()->exists()) {
            return;
        }

        foreach (['Documentation', 'Components', 'Patterns'] as $rootPosition => $rootName) {
            $root = ExampleNode::factory()->create(['name' => $rootName, 'position' => $rootPosition]);

            foreach (range(0, 2) as $childPosition) {
                $child = ExampleNode::factory()->childOf($root)->create(['position' => $childPosition]);

                foreach (range(0, 1) as $leafPosition) {
                    ExampleNode::factory()->childOf($child)->create(['position' => $leafPosition]);
                }
            }
        }
    }
}

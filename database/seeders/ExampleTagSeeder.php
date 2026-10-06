<?php

declare(strict_types=1);

namespace VmEngine\Example\Seeders;

use Illuminate\Database\Seeder;
use VmEngine\Example\Models\ExampleTag;

class ExampleTagSeeder extends Seeder
{
    /** @var array<string, string> name => color */
    private const TAGS = [
        'Laravel' => '#ef4444',
        'Livewire' => '#ec4899',
        'Alpine' => '#14b8a6',
        'Tailwind' => '#06b6d4',
        'Pest' => '#a855f7',
        'PHP' => '#6366f1',
        'UI' => '#f59e0b',
        'API' => '#22c55e',
    ];

    public function run(): void
    {
        foreach (self::TAGS as $name => $color) {
            ExampleTag::query()->firstOrCreate(['name' => $name], ['color' => $color]);
        }
    }
}

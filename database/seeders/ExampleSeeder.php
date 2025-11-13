<?php

namespace VmEngine\Example\Seeders;

use Illuminate\Database\Seeder;
use VmEngine\Example\Models\Example;

class ExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Example::factory(100)->create();
    }
}

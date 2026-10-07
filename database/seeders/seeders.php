<?php

declare(strict_types=1);

use VmEngine\Example\Seeders\ExampleAuthorRoleSeeder;
use VmEngine\Example\Seeders\ExampleCategorySeeder;
use VmEngine\Example\Seeders\ExampleNodeSeeder;
use VmEngine\Example\Seeders\ExampleSeeder;
use VmEngine\Example\Seeders\ExampleTagSeeder;

return [
    ExampleCategorySeeder::class,
    ExampleTagSeeder::class,
    ExampleSeeder::class,
    ExampleNodeSeeder::class,
    ExampleAuthorRoleSeeder::class,
];

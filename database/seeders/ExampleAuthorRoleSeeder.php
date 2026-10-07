<?php

declare(strict_types=1);

namespace VmEngine\Example\Seeders;

use Illuminate\Database\Seeder;
use VmEngine\Example\Support\ExampleAuthorRole;

class ExampleAuthorRoleSeeder extends Seeder
{
    public function run(): void
    {
        ExampleAuthorRole::ensure();
    }
}

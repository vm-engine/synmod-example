<?php

declare(strict_types=1);

use VmEngine\Example\Catalog\PatternCatalog;

it('has a breadcrumb title for every pattern group', function (string $group) {
    // PatternPage builds its breadcrumb from example::{group}.title.
    expect(__("example::{$group}.title"))->not->toBe("example::{$group}.title");
})->with(PatternCatalog::GROUPS);

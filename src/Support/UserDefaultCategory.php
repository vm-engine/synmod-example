<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\Example\Models\ExampleCategory;

/**
 * Reads the user field extension users.example_default_category_id,
 * ignoring ids whose category no longer exists.
 */
final class UserDefaultCategory
{
    public const FIELD = 'example_default_category_id';

    public static function forCurrentUser(): ?int
    {
        $id = auth_user()?->getAttribute(self::FIELD);

        return is_numeric($id) && ExampleCategory::query()->whereKey((int) $id)->exists() ? (int) $id : null;
    }
}

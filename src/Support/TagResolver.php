<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\Example\Models\ExampleTag;

/**
 * Turns the values of a creatable tag select into tag ids: integers (or numeric
 * strings) that match a tag are kept, other strings are matched by name
 * (case-insensitive) or created. Duplicates and invalid entries are dropped.
 */
final class TagResolver
{
    private const MAX_NAME = 50;

    /**
     * @param  array<int, mixed>  $values
     * @return list<int>
     */
    public static function resolve(array $values): array
    {
        $ids = [];

        foreach ($values as $value) {
            $id = self::resolveOne($value);

            if ($id !== null && ! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private static function resolveOne(mixed $value): ?int
    {
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $existing = ExampleTag::query()->whereKey((int) $value)->value('id');

            if ($existing !== null) {
                return (int) $existing;
            }

            if (is_int($value)) {
                return null;
            }
        }

        if (! is_string($value)) {
            return null;
        }

        $name = trim($value);

        if ($name === '' || mb_strlen($name) > self::MAX_NAME) {
            return null;
        }

        $match = ExampleTag::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->value('id');

        return $match !== null ? (int) $match : ExampleTag::query()->create(['name' => $name])->id;
    }
}

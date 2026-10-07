<?php

declare(strict_types=1);

namespace VmEngine\Example\Exports;

use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Synapse\Services\Excel\Contracts\ColumnTransformer;

/**
 * Serializable Excel transformer: status enum (or its raw value) → translated label.
 */
final class ExampleStatusLabel implements ColumnTransformer
{
    public function transform(mixed $value, mixed $record): mixed
    {
        if ($value instanceof ExampleStatus) {
            return $value->label();
        }

        return is_string($value) ? (ExampleStatus::tryFrom($value)?->label() ?? '') : '';
    }
}

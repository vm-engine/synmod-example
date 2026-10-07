<?php

declare(strict_types=1);

namespace VmEngine\Example\Enums;

enum ExampleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';

    public function label(): string
    {
        return __('example::labels.status_'.$this->value);
    }

    /**
     * Synapse color token (badge, kanban column, calendar event).
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Review => 'warning',
            self::Published => 'success',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}

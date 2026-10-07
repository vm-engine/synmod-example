<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use Illuminate\Validation\Rule;
use VmEngine\Example\Enums\ExampleStatus;

/**
 * Runtime module settings, stored in dbconf as one JSON blob (one query per
 * request). Every value is validated on read and falls back to its default,
 * so a hand-edited or stale row can never break a page.
 *
 * @phpstan-type Settings array{perPage: int, showDueColumn: bool, defaultStatus: string, maxAttachments: int, wipLimit: int}
 */
final class ExampleSettings
{
    private const CONFIG_KEY = 'example.settings';

    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [10, 15, 25, 50];

    private const DEFAULTS = [
        'perPage' => 15,
        'showDueColumn' => true,
        'defaultStatus' => 'draft',
        'maxAttachments' => 10,
        'wipLimit' => 0,
    ];

    /**
     * @return Settings
     */
    public static function all(): array
    {
        $stored = json_decode((string) dbconf(self::CONFIG_KEY, ''), true);
        $stored = is_array($stored) ? $stored : [];
        $settings = self::DEFAULTS;

        foreach (self::DEFAULTS as $key => $default) {
            if (array_key_exists($key, $stored) && self::isValid($key, $stored[$key])) {
                $settings[$key] = $stored[$key];
            }
        }

        /** @var Settings */
        return $settings;
    }

    /**
     * Merge the given keys over the current settings (unknown keys dropped).
     *
     * @param  array<string, mixed>  $values
     */
    public static function save(array $values): void
    {
        $merged = array_merge(self::all(), array_intersect_key($values, self::DEFAULTS));

        dbconf([self::CONFIG_KEY => json_encode($merged, JSON_THROW_ON_ERROR)]);
    }

    /**
     * Validation rules for the settings page (same limits as isValid()).
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'perPage' => ['required', 'integer', Rule::in(self::PER_PAGE_OPTIONS)],
            'showDueColumn' => ['boolean'],
            'defaultStatus' => ['required', Rule::enum(ExampleStatus::class)],
            'maxAttachments' => ['required', 'integer', 'between:1,50'],
            'wipLimit' => ['required', 'integer', 'between:0,99'],
        ];
    }

    public static function perPage(): int
    {
        return self::all()['perPage'];
    }

    public static function showDueColumn(): bool
    {
        return self::all()['showDueColumn'];
    }

    public static function defaultStatus(): string
    {
        return self::all()['defaultStatus'];
    }

    public static function maxAttachments(): int
    {
        return self::all()['maxAttachments'];
    }

    public static function wipLimit(): int
    {
        return self::all()['wipLimit'];
    }

    private static function isValid(string $key, mixed $value): bool
    {
        return match ($key) {
            'perPage' => is_int($value) && in_array($value, self::PER_PAGE_OPTIONS, true),
            'showDueColumn' => is_bool($value),
            'defaultStatus' => is_string($value) && ExampleStatus::tryFrom($value) !== null,
            'maxAttachments' => is_int($value) && $value >= 1 && $value <= 50,
            'wipLimit' => is_int($value) && $value >= 0 && $value <= 99,
            default => false,
        };
    }
}

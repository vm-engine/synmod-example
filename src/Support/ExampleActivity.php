<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\Example\Models\Example;

/**
 * Writes example activity to the synapps-auth activity log (module "example",
 * feature "manage"). Only whitelisted columns go into the snapshots — never
 * content, meta or file paths.
 */
final class ExampleActivity
{
    /** Columns recorded in before/after snapshots. */
    public const LOGGED_FIELDS = ['text', 'slug', 'status', 'category_id', 'due_at', 'email', 'position'];

    /** @var list<string> */
    public const ACTIONS = [
        'example.created', 'example.updated', 'example.deleted', 'example.restored',
        'example.force_deleted', 'example.bulk_status', 'example.bulk_deleted', 'example.trash_emptied',
    ];

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function forExample(string $action, Example $example, ?array $before = null, ?array $after = null): void
    {
        self::write($action, ['id' => $example->id, 'title' => $example->text], $before, $after);
    }

    /**
     * Summary entry for writes that bypass model events (bulk query updates).
     *
     * @param  array<string, string|int>  $replace
     */
    public static function summary(string $action, array $replace): void
    {
        self::write($action, $replace, null, $replace);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function snapshot(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(self::LOGGED_FIELDS));
    }

    /**
     * Lang key for an action: "example.created" → "created" (dots would nest).
     */
    public static function langKey(string $action): string
    {
        return substr($action, strlen('example.'));
    }

    /**
     * @param  array<string, string|int>  $replace
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private static function write(string $action, array $replace, ?array $before, ?array $after): void
    {
        if (! auth()->check()) {
            return;
        }

        $logger = log_activity()->module('example')->feature('manage')->action($action)
            ->description(__('example::integrations.log.'.self::langKey($action), $replace));

        if ($before !== null) {
            $logger->metaBefore($before);
        }
        if ($after !== null) {
            $logger->metaAfter($after);
        }

        $logger->log();
    }
}

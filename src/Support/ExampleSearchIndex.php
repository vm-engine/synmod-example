<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\Example\Models\Example;
use VmEngine\Synapse\Facades\SynSearchable;

/**
 * Examples in the backend Ctrl/Cmd+K palette. Title, status, slug and email
 * only — content never leaves the table. A silent no-op until
 * synapps:searchable-setup has created the searchables table.
 */
final class ExampleSearchIndex
{
    public const SOURCE = 'content:example.item';

    public static function put(Example $example): void
    {
        SynSearchable::put(
            source: self::SOURCE,
            key: (string) $example->id,
            title: $example->text,
            description: $example->status->label(),
            route: 'example.show',
            routeParams: ['id' => $example->id],
            icon: 'ph ph-cube',
            acl: 'example.manage',
            keywords: trim($example->slug.' '.$example->email),
        );
    }

    public static function forget(int $id): void
    {
        SynSearchable::forget(self::SOURCE, (string) $id);
    }

    /**
     * @param  iterable<int>  $ids
     */
    public static function forgetMany(iterable $ids): void
    {
        foreach ($ids as $id) {
            self::forget((int) $id);
        }
    }

    /**
     * Rebuild every example entry (trashed examples stay out).
     */
    public static function reindex(): int
    {
        if (! synsearchable()->isEnabled()) {
            return 0;
        }

        SynSearchable::forgetSource(self::SOURCE);
        $count = 0;

        Example::query()->chunkById(200, function ($examples) use (&$count): void {
            foreach ($examples as $example) {
                self::put($example);
                $count++;
            }
        });

        return $count;
    }
}

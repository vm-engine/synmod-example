<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use VmEngine\Example\Models\Example;
use VmEngine\Example\Notifications\ExamplePublished;
use VmEngine\Synapse\Notifications\Notify;

/**
 * Who hears about example events. Publishing notifies every admin except the
 * actor; imports mute per-row notices and send one summary instead.
 */
final class ExampleNotifier
{
    private static bool $muted = false;

    /**
     * Run $callback with publish notifications muted (restores the previous state).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function muted(callable $callback): mixed
    {
        $previous = self::$muted;
        self::$muted = true;

        try {
            return $callback();
        } finally {
            self::$muted = $previous;
        }
    }

    public static function published(Example $example): void
    {
        self::sendPublished(new ExamplePublished($example->id, $example->text));
    }

    public static function bulkPublished(int $count): void
    {
        if ($count > 0) {
            self::sendPublished(new ExamplePublished(null, '', $count));
        }
    }

    private static function sendPublished(ExamplePublished $notification): void
    {
        if (self::$muted) {
            return;
        }

        $notify = Notify::make($notification)->toRoles('admin');

        if (auth()->check()) {
            $notify->except(auth()->user());
        }

        $notify->send();
    }
}

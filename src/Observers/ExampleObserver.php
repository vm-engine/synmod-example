<?php

declare(strict_types=1);

namespace VmEngine\Example\Observers;

use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleActivity;
use VmEngine\Example\Support\ExampleNotifier;
use VmEngine\Example\Support\ExampleSearchIndex;

/**
 * One place for every model-level example write — activity log, global
 * search index and publish notifications (editor, drawer,
 * wizards, quick edit, kanban, trash). Query-builder writes (bulk actions)
 * log their own summary.
 */
class ExampleObserver
{
    public function created(Example $example): void
    {
        ExampleSearchIndex::put($example);

        ExampleActivity::forExample('example.created', $example, null, ExampleActivity::snapshot($example->getAttributes()));

        if ($example->status === ExampleStatus::Published) {
            ExampleNotifier::published($example);
        }
    }

    public function updated(Example $example): void
    {
        ExampleSearchIndex::put($example);

        if ($example->wasChanged('status') && $example->status === ExampleStatus::Published) {
            ExampleNotifier::published($example);
        }

        $after = ExampleActivity::snapshot($example->getChanges());

        if ($after === []) {
            return;
        }

        $before = array_intersect_key($example->getRawOriginal(), $after);

        ExampleActivity::forExample('example.updated', $example, $before, $after);
    }

    public function deleted(Example $example): void
    {
        ExampleSearchIndex::forget($example->id);

        if (! $example->isForceDeleting()) {
            ExampleActivity::forExample('example.deleted', $example);
        }
    }

    public function restored(Example $example): void
    {
        ExampleSearchIndex::put($example);

        ExampleActivity::forExample('example.restored', $example);
    }

    public function forceDeleted(Example $example): void
    {
        ExampleSearchIndex::forget($example->id);

        ExampleActivity::forExample('example.force_deleted', $example, ExampleActivity::snapshot($example->getAttributes()));
    }
}

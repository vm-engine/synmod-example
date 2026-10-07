<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use VmEngine\Example\Models\ExampleTag;

/**
 * Remote search + preload for a creatable tag adv-select.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait HasTagSearch
{
    /**
     * @return list<array{value: int, label: string}>
     */
    public function searchTags(string $query): array
    {
        return ExampleTag::query()
            ->where('name', 'like', '%'.trim($query).'%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name'])
            ->map(fn (ExampleTag $tag): array => ['value' => $tag->id, 'label' => $tag->name])
            ->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return list<array{value: int, label: string}>
     */
    public function loadTags(array $ids): array
    {
        return ExampleTag::query()
            ->whereKey(array_filter($ids, fn ($id): bool => is_int($id) || (is_string($id) && ctype_digit($id))))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ExampleTag $tag): array => ['value' => $tag->id, 'label' => $tag->name])
            ->all();
    }
}

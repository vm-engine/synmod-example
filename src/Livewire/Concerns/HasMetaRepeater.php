<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

/**
 * Key/value repeater actions over $this->form->meta (an ExampleEditorForm).
 * Rows reorder with wire:sort (moveMetaRow handler).
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait HasMetaRepeater
{
    public function addMetaRow(): void
    {
        $this->form->meta[] = ['key' => '', 'value' => ''];
    }

    public function removeMetaRow(int $index): void
    {
        unset($this->form->meta[$index]);
        $this->form->meta = array_values($this->form->meta);
    }

    /**
     * wire:sort handler: $item is the row index (wire:sort:item), $position the new index.
     */
    public function moveMetaRow(int|string $item, int $position): void
    {
        $index = (int) $item;

        if (! isset($this->form->meta[$index])) {
            return;
        }

        $row = $this->form->meta[$index];
        unset($this->form->meta[$index]);
        $rows = array_values($this->form->meta);
        array_splice($rows, max(0, min($position, count($rows))), 0, [$row]);
        $this->form->meta = $rows;
    }

    public function toggleRawJson(): void
    {
        $this->resetErrorBag('form.metaJson');
        $this->form->toggleRawJson();
    }
}

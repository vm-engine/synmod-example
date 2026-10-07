<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleSettings;
use VmEngine\Example\Support\ExampleStats;

new class extends Component
{
    use PagePatternPage;

    private const CARDS_PER_COLUMN = 30;

    public ?int $pendingMoveId = null;

    public int $pendingPosition = 0;

    public string $publishNote = '';

    public function title(): string
    {
        return __('example::pages.kanban');
    }

    /**
     * x-synapse-kanban handler: $item = example id, $column = destination status.
     */
    public function moveItem(int|string $item, int $position, string $column): void
    {
        if (! auth()->user()?->can('example.manage.update')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::pages.not_allowed'));

            return;
        }

        $status = ExampleStatus::tryFrom($column);
        $example = Example::query()->find((int) $item);

        if ($status === null || $example === null) {
            return;
        }

        // Same rule as the editor: Published needs a publish note.
        if ($status === ExampleStatus::Published && $example->status !== ExampleStatus::Published && empty($example->meta['publish_note'])) {
            $this->pendingMoveId = $example->id;
            $this->pendingPosition = $position;
            $this->publishNote = '';
            $this->resetValidation();
            $this->dispatch('open-modal-publish-note');

            return;
        }

        $this->applyMove($example, $status, $position);
    }

    public function confirmPublish(): void
    {
        if (! auth()->user()?->can('example.manage.update')) {
            return;
        }

        $this->validate(['publishNote' => ['required', 'string', 'max:500']]);

        $example = Example::query()->find($this->pendingMoveId);

        if ($example !== null) {
            $example->meta = [...($example->meta ?? []), 'publish_note' => trim($this->publishNote)];
            $this->applyMove($example, ExampleStatus::Published, $this->pendingPosition);
        }

        $this->cancelPublish();
    }

    public function cancelPublish(): void
    {
        $this->reset('pendingMoveId', 'pendingPosition', 'publishNote');
        $this->dispatch('close-modal-publish-note');
    }

    /**
     * @return array<string, Collection<int, Example>>
     */
    #[Computed()]
    public function columns(): array
    {
        $columns = [];

        foreach (ExampleStatus::cases() as $status) {
            $columns[$status->value] = Example::query()
                ->with(['category', 'tags'])
                ->where('status', $status->value)
                ->orderBy('position')->orderByDesc('id')
                ->limit(self::CARDS_PER_COLUMN)
                ->get();
        }

        return $columns;
    }

    /**
     * @return array<string, int>
     */
    #[Computed()]
    public function counts(): array
    {
        return ExampleStats::statusCounts();
    }

    #[Computed()]
    public function wipLimit(): int
    {
        return ExampleSettings::wipLimit();
    }

    /**
     * @return list<ExampleStatus>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::cases();
    }

    /**
     * ponytail: rewrites positions for the whole destination column — fine at
     * demo scale; switch to gap-based positions if columns grow to thousands.
     */
    private function applyMove(Example $example, ExampleStatus $status, int $position): void
    {
        DB::transaction(function () use ($example, $status, $position): void {
            $ids = Example::query()->where('status', $status->value)->whereKeyNot($example->id)
                ->orderBy('position')->orderByDesc('id')->pluck('id')->all();
            array_splice($ids, max(0, min($position, count($ids))), 0, [$example->id]);

            $example->status = $status;
            $example->save();

            foreach ($ids as $index => $id) {
                Example::query()->whereKey($id)->update(['position' => $index]);
            }
        });

        unset($this->columns, $this->counts);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::pages.moved'));
    }
};

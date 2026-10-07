<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\ExampleNode;

new class extends Component
{
    use PagePatternPage;

    #[Url()]
    public ?int $node = null;

    public string $name = '';

    public string $description = '';

    public function mount(): void
    {
        $this->selectNode($this->node);
    }

    public function title(): string
    {
        return __('example::pages.node_browser');
    }

    public function selectNode(?int $id): void
    {
        $this->resetValidation();
        $selected = $id === null ? null : ($this->nodesById[$id] ?? null);

        $this->node = $selected?->id;
        $this->name = $selected->name ?? '';
        $this->description = (string) ($selected->description ?? '');
    }

    public function save(): void
    {
        if (! auth()->user()?->can('example.node.update')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::pages.not_allowed'));

            return;
        }

        $selected = $this->node === null ? null : ExampleNode::query()->find($this->node);

        if ($selected === null) {
            $this->selectNode(null);

            return;
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $selected->update(['name' => $data['name'], 'description' => $data['description'] ?: null]);
        unset($this->nodesById, $this->childrenMap);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::pages.node_saved'));
    }

    /**
     * All nodes, one query.
     *
     * @return array<int, ExampleNode>
     */
    #[Computed()]
    public function nodesById(): array
    {
        return ExampleNode::query()->orderBy('position')->orderBy('id')->get()->keyBy('id')->all();
    }

    /**
     * Children grouped by parent id (0 = roots).
     *
     * @return array<int, list<ExampleNode>>
     */
    #[Computed()]
    public function childrenMap(): array
    {
        $map = [];

        foreach ($this->nodesById as $item) {
            $map[$item->parent_id ?? 0][] = $item;
        }

        return $map;
    }

    /**
     * Root → selected node.
     *
     * @return list<ExampleNode>
     */
    public function path(): array
    {
        $path = [];
        $current = $this->node === null ? null : ($this->nodesById[$this->node] ?? null);

        while ($current !== null && count($path) < 50) {
            array_unshift($path, $current);
            $current = $current->parent_id === null ? null : ($this->nodesById[$current->parent_id] ?? null);
        }

        return $path;
    }
};

<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Models\ExampleNode;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    /** Parent key ("root" or a node id) whose inline add input is open. */
    public ?string $addingTo = null;

    public string $newName = '';

    public ?int $renamingId = null;

    public string $renameValue = '';

    public function mount(): void
    {
        synav()->setActiveMenu('example.nodes');
    }

    public function title(): string
    {
        return __('example::lists.nodes');
    }

    /**
     * wire:sort handler. $parentKey is the drop list's wire:sort:group-id ("root" or a node id).
     */
    public function moveNode(int|string $id, int $position, string $parentKey): void
    {
        if (! $this->allowed('example.node.update')) {
            return;
        }

        $node = ExampleNode::query()->find((int) $id);
        $parent = $parentKey === 'root' ? null : ExampleNode::query()->find((int) $parentKey);

        if ($node === null || ($parentKey !== 'root' && $parent === null)) {
            return;
        }

        if ($parent !== null && $parent->isWithinBranchOf($node->id)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.node_cycle'));

            return;
        }

        DB::transaction(function () use ($node, $parent, $position): void {
            $oldParentId = $node->parent_id;
            $node->update(['parent_id' => $parent?->id]);

            $this->renumber($oldParentId);

            $siblings = ExampleNode::query()
                ->where('parent_id', $parent?->id)
                ->whereKeyNot($node->id)
                ->orderBy('position')
                ->pluck('id')
                ->all();
            array_splice($siblings, max(0, min($position, count($siblings))), 0, [$node->id]);

            foreach ($siblings as $index => $siblingId) {
                ExampleNode::query()->whereKey($siblingId)->update(['position' => $index]);
            }
        });

        unset($this->childrenByParent);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.node_moved'));
    }

    public function startAdd(string $parentKey): void
    {
        $this->addingTo = $parentKey;
        $this->newName = '';
        $this->resetValidation();
    }

    public function addNode(): void
    {
        if (! $this->allowed('example.node.create') || $this->addingTo === null) {
            return;
        }

        $this->validate(['newName' => ['required', 'string', 'max:100']]);

        $parentId = $this->addingTo === 'root' ? null : ExampleNode::query()->findOrFail((int) $this->addingTo)->id;

        ExampleNode::query()->create([
            'parent_id' => $parentId,
            'name' => $this->newName,
            'position' => (int) ExampleNode::query()->where('parent_id', $parentId)->max('position') + 1,
        ]);

        $this->reset('addingTo', 'newName');
        $this->saved();
    }

    public function startRename(int $id): void
    {
        $node = ExampleNode::query()->findOrFail($id);
        $this->renamingId = $node->id;
        $this->renameValue = $node->name;
        $this->resetValidation();
    }

    public function saveRename(): void
    {
        if (! $this->allowed('example.node.update') || $this->renamingId === null) {
            return;
        }

        $this->validate(['renameValue' => ['required', 'string', 'max:100']]);
        ExampleNode::query()->findOrFail($this->renamingId)->update(['name' => $this->renameValue]);
        $this->reset('renamingId', 'renameValue');
        $this->saved();
    }

    public function cancelInline(): void
    {
        $this->reset('addingTo', 'newName', 'renamingId', 'renameValue');
        $this->resetValidation();
    }

    public function delete(string $token): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        if (! $this->allowed('example.node.delete')) {
            return;
        }

        $id = ExampleNode::validateDeleteToken($token);
        $node = $id ? ExampleNode::query()->find($id) : null;

        if ($node === null) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::labels.invalid_delete_token'));

            return;
        }

        $parentId = $node->parent_id;
        $node->delete();
        $this->renumber($parentId);

        unset($this->childrenByParent);
        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.node_deleted'));
    }

    /**
     * All nodes in one query, grouped by parent key ("root" for top level).
     *
     * @return Collection<string, Illuminate\Database\Eloquent\Collection<int, ExampleNode>>
     */
    #[Computed()]
    public function childrenByParent(): Collection
    {
        return ExampleNode::query()->orderBy('position')->orderBy('id')->get()
            ->groupBy(fn (ExampleNode $node): string => $node->parent_id === null ? 'root' : (string) $node->parent_id);
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(label: __('example::lists.nodes'), icon: 'ph ph-tree-structure');
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }

    private function renumber(?int $parentId): void
    {
        $ids = ExampleNode::query()->where('parent_id', $parentId)->orderBy('position')->pluck('id')->all();

        foreach ($ids as $index => $nodeId) {
            ExampleNode::query()->whereKey($nodeId)->update(['position' => $index]);
        }
    }

    private function allowed(string $acl): bool
    {
        if (auth()->user()?->can($acl)) {
            return true;
        }

        $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.not_allowed'));

        return false;
    }

    private function saved(): void
    {
        unset($this->childrenByParent);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.node_saved'));
    }
};

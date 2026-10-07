<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use ListPatternPage;
    use WithPagination;

    #[Url()]
    public string $view = 'active';

    public function title(): string
    {
        return __('example::lists.trashed');
    }

    public function setView(string $view): void
    {
        $this->view = $view === 'trash' ? 'trash' : 'active';
        $this->resetPage();
    }

    public function delete(string $token): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        $example = $this->resolve($token, trashed: false);
        $example?->delete();
        $this->done($example !== null, __('example::lists.moved_to_trash'));
    }

    public function restore(string $token): void
    {
        $example = $this->resolve($token, trashed: true);
        $example?->restore();
        $this->done($example !== null, __('example::lists.restored'));
    }

    public function forceDelete(string $token): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        $example = $this->resolve($token, trashed: true);
        $example?->forceDelete();
        $this->done($example !== null, __('example::lists.force_deleted'));
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()
            ->when($this->view === 'trash', fn ($query) => $query->onlyTrashed())
            ->with('category')
            ->orderByDesc($this->view === 'trash' ? 'deleted_at' : 'id')
            ->paginate(15);
    }

    private function resolve(string $token, bool $trashed): ?Example
    {
        // Route middleware does not re-run on Livewire action requests, so authorize here.
        if (! $this->allowed('example.manage.delete')) {
            return null;
        }

        $id = Example::validateDeleteToken($token);

        if (! $id) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::labels.invalid_delete_token'));

            return null;
        }

        $query = $trashed ? Example::onlyTrashed() : Example::query();

        return $query->find($id);
    }

    private function done(bool $ok, string $message): void
    {
        if (! $ok) {
            return;
        }

        unset($this->examples);
        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: $message);
    }

    private function allowed(string $acl): bool
    {
        if (auth()->user()?->can($acl)) {
            return true;
        }

        $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.not_allowed'));

        return false;
    }
};

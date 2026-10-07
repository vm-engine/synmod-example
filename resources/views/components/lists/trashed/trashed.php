<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleActivity;
use VmEngine\SynAuth\Services\OtpProtectionService;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;
use VmEngine\SynAuth\Traits\WithOtpProtection;

new class extends Component
{
    use GuardsBackendPermission;
    use ListPatternPage;
    use WithOtpProtection;
    use WithPagination;

    private const EMPTY_TRASH_PURPOSE = 'example-empty-trash';

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

    public function emptyTrash(): void
    {
        if (! $this->guardAction('example.manage.delete')) {
            return;
        }

        if (! Example::onlyTrashed()->exists()) {
            $this->dispatch('notify', variant: 'info', title: 'Info', message: __('example::integrations.empty_trash_none'));

            return;
        }

        $this->requireOtp(self::EMPTY_TRASH_PURPOSE);
    }

    #[On('otp-verified')]
    public function handleOtpVerified(string $token, string $purpose): void
    {
        if ($purpose !== self::EMPTY_TRASH_PURPOSE) {
            return;
        }

        // Single-use token, bound to this user AND this purpose.
        $data = app(OtpProtectionService::class)->validateActionToken($token, (int) auth()->id());

        if ($data === false || $data['purpose'] !== self::EMPTY_TRASH_PURPOSE) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::integrations.otp_invalid'));

            return;
        }

        if (! $this->guardAction('example.manage.delete')) {
            return;
        }

        $count = 0;

        // Per model, so forceDeleting/forceDeleted remove files and log each row.
        DB::transaction(function () use (&$count): void {
            Example::onlyTrashed()->chunkById(100, function ($examples) use (&$count): void {
                foreach ($examples as $example) {
                    $example->forceDelete();
                    $count++;
                }
            });
        });

        ExampleActivity::summary('example.trash_emptied', ['count' => $count]);
        unset($this->examples);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::integrations.empty_trash_done', ['count' => $count]));
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
        if (! $this->guardAction('example.manage.delete')) {
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
};

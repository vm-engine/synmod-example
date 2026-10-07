<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleAuthorRole;

new class extends Component
{
    use IntegrationPatternPage;

    public ?int $renamingId = null;

    public string $renameText = '';

    public function title(): string
    {
        return __('example::integrations.abac');
    }

    public function startRename(int $id): void
    {
        $example = Example::query()->find($id);
        $this->resetValidation();
        $this->renamingId = $example?->id;
        $this->renameText = $example->text ?? '';
    }

    public function cancelRename(): void
    {
        $this->reset('renamingId', 'renameText');
    }

    /**
     * ABAC: the record goes into the check, so a conditional row can match.
     */
    public function rename(): void
    {
        $example = $this->renamingId === null ? null : Example::query()->find($this->renamingId);

        if ($example === null || ! auth_user()?->can('example.manage.update', $example)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::integrations.rename_denied'));

            return;
        }

        $this->validate(['renameText' => ['required', 'string', 'max:255']]);
        $example->update(['text' => trim($this->renameText)]);
        $this->cancelRename();
        unset($this->rows);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::integrations.renamed'));
    }

    /**
     * @return list<array{example: Example, allowed: bool, source: string}>
     */
    #[Computed()]
    public function rows(): array
    {
        $user = auth_user();

        /** @var Collection<int, Example> $examples */
        $examples = Example::query()->with('creator')->latest('id')->limit(15)->get();

        return $examples->map(function (Example $example) use ($user): array {
            $result = $user?->resolvePermission('example.manage.update', $example);

            return ['example' => $example, 'allowed' => (bool) $result?->allowed, 'source' => $result->source ?? 'guest'];
        })->all();
    }

    #[Computed()]
    public function conditionJson(): string
    {
        return (string) json_encode(ExampleAuthorRole::OWN_RECORDS, JSON_UNESCAPED_SLASHES);
    }
};

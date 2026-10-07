<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleSettings;
use VmEngine\Example\Support\UserDefaultCategory;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;
    use PagePatternPage;

    #[Url()]
    public string $month = '';

    public string $quickDate = '';

    public string $quickText = '';

    public string $quickEmail = '';

    public string $quickStatus = 'draft';

    public function mount(): void
    {
        if (! $this->isValidMonth($this->month)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function title(): string
    {
        return __('example::pages.calendar');
    }

    public function setMonth(string $month): void
    {
        if ($this->isValidMonth($month)) {
            $this->month = $month;
            unset($this->events);
        }
    }

    public function openEvent(string $id): void
    {
        $this->redirect(backend_route('example.show', ['id' => (int) $id]), navigate: true);
    }

    public function createOn(string $date): void
    {
        if (! $this->isValidDate($date)) {
            return;
        }

        $this->resetValidation();
        $this->reset('quickText', 'quickEmail');
        $this->quickStatus = ExampleSettings::defaultStatus();
        $this->quickDate = $date;
        $this->dispatch('open-modal-calendar-create');
    }

    public function saveQuick(): void
    {
        if (! $this->guardAction('example.manage.create')) {
            return;
        }

        $data = $this->validate([
            'quickText' => ['required', 'string', 'max:255'],
            'quickEmail' => ['required', 'email', 'max:255'],
            'quickStatus' => ['required', Rule::enum(ExampleStatus::class)],
            'quickDate' => ['required', 'date_format:Y-m-d'],
        ]);

        Example::query()->create([
            'text' => $data['quickText'],
            'email' => $data['quickEmail'],
            'status' => $data['quickStatus'],
            'due_at' => $data['quickDate'],
            'category_id' => UserDefaultCategory::forCurrentUser(),
        ]);

        unset($this->events);
        $this->dispatch('close-modal-calendar-create');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::pages.created'));
    }

    /**
     * @return list<array{id: int, title: string, date: string, color: string}>
     */
    #[Computed()]
    public function events(): array
    {
        $start = Carbon::createFromFormat('!Y-m', $this->month)->startOfMonth();

        return Example::query()
            ->whereBetween('due_at', [$start, $start->copy()->endOfMonth()])
            ->orderBy('due_at')
            ->get(['id', 'text', 'status', 'due_at'])
            ->map(fn (Example $example): array => [
                'id' => $example->id,
                'title' => $example->text,
                'date' => $example->due_at->toDateString(),
                'color' => $example->status->color(),
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
    }

    private function isValidMonth(string $month): bool
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1;
    }

    private function isValidDate(string $date): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1
            && checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4));
    }
};

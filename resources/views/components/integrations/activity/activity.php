<?php

declare(strict_types=1);

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\Example\Support\ExampleActivity;
use VmEngine\SynAuth\Models\UserActivity;

new class extends Component
{
    use IntegrationPatternPage;
    use WithPagination;

    #[Url()]
    public string $filterAction = '';

    public function title(): string
    {
        return __('example::integrations.activity');
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, UserActivity>
     */
    #[Computed()]
    public function activities(): LengthAwarePaginator
    {
        $action = in_array($this->filterAction, ExampleActivity::ACTIONS, true) ? $this->filterAction : null;

        return UserActivity::query()
            ->forModule('example')
            ->when($action !== null, fn ($query) => $query->forAction($action))
            ->with('user')
            ->latest('id')
            ->paginate(15);
    }

    /**
     * @return array<string, string>
     */
    #[Computed()]
    public function actionLabels(): array
    {
        $labels = [];

        foreach (ExampleActivity::ACTIONS as $action) {
            $labels[$action] = __('example::integrations.actions.'.ExampleActivity::langKey($action));
        }

        return $labels;
    }
};

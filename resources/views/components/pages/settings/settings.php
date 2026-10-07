<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Support\ExampleSettings;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    /** Which settings each tab saves. */
    private const TAB_FIELDS = [
        'lists' => ['perPage', 'showDueColumn'],
        'editor' => ['defaultStatus', 'maxAttachments'],
        'board' => ['wipLimit'],
    ];

    public string $activeTab = 'lists';

    public int $perPage = 15;

    public bool $showDueColumn = true;

    public string $defaultStatus = 'draft';

    public int $maxAttachments = 10;

    public int $wipLimit = 0;

    public function mount(): void
    {
        synav()->setActiveMenu('example.settings');
        $this->fill(ExampleSettings::all());
    }

    public function title(): string
    {
        return __('example::pages.settings');
    }

    public function selectTab(string $tab): void
    {
        if (array_key_exists($tab, self::TAB_FIELDS)) {
            $this->activeTab = $tab;
        }
    }

    public function saveLists(): void
    {
        $this->saveTab('lists');
    }

    public function saveEditor(): void
    {
        $this->saveTab('editor');
    }

    public function saveBoard(): void
    {
        $this->saveTab('board');
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(label: __('example::menu.be.parent'), url: backend_route('example.index'), icon: 'ph ph-cube')
            ->add(label: $this->title());
    }

    /**
     * @return list<int>
     */
    #[Computed()]
    public function perPageOptions(): array
    {
        return ExampleSettings::PER_PAGE_OPTIONS;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }

    private function saveTab(string $tab): void
    {
        if (! auth()->user()?->can('example.settings.update')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::pages.not_allowed'));

            return;
        }

        $data = $this->validate(array_intersect_key(ExampleSettings::rules(), array_flip(self::TAB_FIELDS[$tab])));

        ExampleSettings::save($data);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::pages.settings_saved'));
    }
};

<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use PagePatternPage;

    private const TABS = ['overview', 'attachments', 'related'];

    #[Locked]
    public ?int $exampleId = null;

    #[Url()]
    public string $tab = 'overview';

    public function mount(?int $id = null): void
    {
        if (! in_array($this->tab, self::TABS, true)) {
            $this->tab = 'overview';
        }

        if ($id === null) {
            $this->exampleId = Example::query()->latest('id')->value('id');

            return;
        }

        if (! Example::query()->whereKey($id)->exists()) {
            session()->flash('danger', __('example::pages.not_found'));
            $this->redirect(backend_route('example.index'), navigate: true);

            return;
        }

        $this->exampleId = $id;
    }

    public function title(): string
    {
        return $this->example?->text ?? __('example::pages.detail');
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, self::TABS, true)) {
            $this->tab = $tab;
        }
    }

    #[Computed()]
    public function example(): ?Example
    {
        return $this->exampleId === null ? null : Example::query()->with(['category', 'tags'])->find($this->exampleId);
    }

    /**
     * @return array<string, string>
     */
    #[Computed()]
    public function tabLabels(): array
    {
        return [
            'overview' => __('example::pages.tab_overview'),
            'attachments' => __('example::pages.tab_attachments'),
            'related' => __('example::pages.tab_related'),
        ];
    }
};

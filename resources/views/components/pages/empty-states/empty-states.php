<?php

declare(strict_types=1);

use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;

new class extends Component
{
    use PagePatternPage;

    public function title(): string
    {
        return __('example::pages.empty_states');
    }

    /**
     * Demo handler for the no-results variant's action.
     */
    public function clearFilters(): void
    {
        $this->dispatch('notify', variant: 'info', title: 'Info', message: __('example::pages.clear_filters'));
    }
};

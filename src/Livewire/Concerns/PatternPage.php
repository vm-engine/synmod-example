<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use Livewire\Attributes\Computed;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

/**
 * Shared shell for pattern demo pages reached from the Pattern Catalog: the
 * catalog stays the active menu item and breadcrumbs read
 * "Pattern Catalog › <group> patterns › <page>".
 *
 * Hosts implement title() and patternGroup() ('lists' | 'forms').
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait PatternPage
{
    abstract public function title(): string;

    abstract public function patternGroup(): string;

    public function mountPatternPage(): void
    {
        synav()->setActiveMenu('example.catalog');
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('example::catalog.title'),
            url: backend_route('example.catalog'),
            icon: 'ph ph-squares-four',
        )->add(
            label: __('example::'.$this->patternGroup().'.title'),
            url: backend_route('example.catalog').'?group='.$this->patternGroup(),
        )->add(label: $this->title());
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
}

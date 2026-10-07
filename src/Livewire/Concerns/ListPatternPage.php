<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use Livewire\Attributes\Computed;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

/**
 * Shared shell for the list-pattern demo pages: they are reached from the
 * Pattern Catalog, so the catalog stays the active menu item and the
 * breadcrumbs read "Pattern Catalog › List patterns › <page>".
 *
 * Hosts implement title().

 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait ListPatternPage
{
    abstract public function title(): string;

    public function mountListPatternPage(): void
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
            label: __('example::lists.title'),
            url: backend_route('example.catalog').'?group=lists',
        )->add(label: $this->title());
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
}

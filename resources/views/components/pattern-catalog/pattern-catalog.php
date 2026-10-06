<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use VmEngine\Example\Catalog\PatternCatalog;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    #[Url()]
    public string $q = '';

    #[Url()]
    public string $group = 'all';

    #[Url()]
    public bool $builtOnly = false;

    public function mount(): void
    {
        synav()->setActiveMenu('example.catalog');

        if (! in_array($this->group, PatternCatalog::GROUPS, true)) {
            $this->group = 'all';
        }
    }

    public function title(): string
    {
        return __('example::catalog.title');
    }

    public function setGroup(string $group): void
    {
        $this->group = in_array($group, PatternCatalog::GROUPS, true) ? $group : 'all';
    }

    /**
     * Registry entries matching the search text, group chip and built-only toggle.
     *
     * @return list<array{key: string, group: string, title: string, description: string, status: string, route: string|null, sources: list<string>, subProject: int}>
     */
    #[Computed()]
    public function patterns(): array
    {
        $needle = mb_strtolower(trim($this->q));

        return array_values(array_filter(
            PatternCatalog::patterns(),
            fn (array $pattern): bool => ($this->group === 'all' || $pattern['group'] === $this->group)
                && (! $this->builtOnly || $pattern['status'] === 'built')
                && ($needle === '' || str_contains(mb_strtolower($pattern['title'].' '.$pattern['description'].' '.$pattern['key']), $needle)),
        ));
    }

    /**
     * Group chips: key, translated label and total pattern count (unfiltered).
     *
     * @return list<array{key: string, label: string, count: int}>
     */
    #[Computed()]
    public function groupChips(): array
    {
        $counts = array_count_values(array_column(PatternCatalog::patterns(), 'group'));

        return array_map(fn (string $group): array => [
            'key' => $group,
            'label' => __('example::catalog.groups.'.$group),
            'count' => $counts[$group] ?? 0,
        ], PatternCatalog::GROUPS);
    }

    /**
     * @return array{built: int, total: int, percent: int}
     */
    #[Computed()]
    public function progress(): array
    {
        $all = PatternCatalog::patterns();
        $built = count(array_filter($all, fn (array $pattern): bool => $pattern['status'] === 'built'));

        return [
            'built' => $built,
            'total' => count($all),
            'percent' => (int) round($built / max(1, count($all)) * 100),
        ];
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('example::catalog.title'),
            icon: 'ph ph-squares-four',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};

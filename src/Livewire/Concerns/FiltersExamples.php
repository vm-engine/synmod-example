<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use Livewire\Attributes\Url;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Exports\ExampleExportQuery;

/**
 * URL-backed example filters for Livewire pages. Values are normalized before
 * they reach the query, so a hand-edited URL can't inject anything.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait FiltersExamples
{
    #[Url()]
    public string $q = '';

    #[Url()]
    public string $filterStatus = '';

    #[Url()]
    public string $filterCategory = '';

    #[Url()]
    public string $dueFrom = '';

    #[Url()]
    public string $dueTo = '';

    public function resetFilters(): void
    {
        $this->reset('q', 'filterStatus', 'filterCategory', 'dueFrom', 'dueTo');
    }

    /**
     * @return array{search: string, status: string|null, categoryId: int|null, dueFrom: string|null, dueTo: string|null}
     */
    protected function filterValues(): array
    {
        return [
            'search' => trim($this->q),
            'status' => ExampleStatus::tryFrom($this->filterStatus)?->value,
            'categoryId' => ctype_digit($this->filterCategory) ? (int) $this->filterCategory : null,
            'dueFrom' => $this->validDate($this->dueFrom),
            'dueTo' => $this->validDate($this->dueTo),
        ];
    }

    protected function exportQuery(): ExampleExportQuery
    {
        return new ExampleExportQuery(...$this->filterValues());
    }

    private function validDate(string $value): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))
            ? $value
            : null;
    }
}

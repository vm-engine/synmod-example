<?php

declare(strict_types=1);

namespace VmEngine\Example\Exports;

use Illuminate\Database\Eloquent\Builder;
use VmEngine\Example\Models\Example;
use VmEngine\Synapse\Services\Excel\Contracts\ExportQuery;

/**
 * The single source of example filtering: used on screen by the inline-filter
 * and export pages, and serialized into queued exports (constructor args are
 * the ExcelExporter::withFilters() keys).
 */
final class ExampleExportQuery implements ExportQuery
{
    public function __construct(
        public readonly string $search = '',
        public readonly ?string $status = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $dueFrom = null,
        public readonly ?string $dueTo = null,
    ) {}

    /**
     * @return Builder<Example>
     */
    public function __invoke(): Builder
    {
        return Example::query()
            ->with('category')
            ->when(mb_strlen($this->search) > 2, fn (Builder $query) => $query->search($this->search))
            ->when($this->status !== null, fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->categoryId !== null, fn (Builder $query) => $query->where('category_id', $this->categoryId))
            ->when($this->dueFrom !== null, fn (Builder $query) => $query->whereDate('due_at', '>=', $this->dueFrom))
            ->when($this->dueTo !== null, fn (Builder $query) => $query->whereDate('due_at', '<=', $this->dueTo))
            ->orderBy('id');
    }
}

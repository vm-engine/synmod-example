<?php

declare(strict_types=1);

namespace VmEngine\Example\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

/**
 * Aggregates for the dashboard, report and print view. Grouped queries only —
 * no per-row loading.
 *
 * @phpstan-type Matrix array{rows: list<array{category: string, counts: array<string, int>, total: int}>, totals: array<string, int>, total: int}
 */
final class ExampleStats
{
    /**
     * @param  Builder<Example>|null  $query
     * @return array<string, int>
     */
    public static function statusCounts(?Builder $query = null): array
    {
        $counts = ($query ?? Example::query())->reorder()->toBase()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return self::fillStatuses(fn (string $status): int => (int) ($counts[$status] ?? 0));
    }

    public static function overdueCount(): int
    {
        return Example::query()
            ->whereDate('due_at', '<', today())
            ->where('status', '!=', ExampleStatus::Published->value)
            ->count();
    }

    public static function dueThisMonthCount(): int
    {
        return Example::query()
            ->whereBetween('due_at', [today()->startOfMonth(), today()->endOfMonth()])
            ->count();
    }

    /**
     * Due examples per week (Monday start), from this week forward.
     *
     * @return array{labels: list<string>, counts: list<int>}
     */
    public static function duePerWeek(int $weeks = 8): array
    {
        $start = today()->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addWeeks($weeks)->subDay()->endOfDay();
        $counts = array_fill(0, $weeks, 0);

        $dates = Example::query()->whereBetween('due_at', [$start, $end])->pluck('due_at');

        foreach ($dates as $date) {
            $counts[intdiv((int) $start->diffInDays(Carbon::parse($date)->startOfDay()), 7)]++;
        }

        $labels = [];
        for ($week = 0; $week < $weeks; $week++) {
            $labels[] = $start->copy()->addWeeks($week)->format('M j');
        }

        return ['labels' => $labels, 'counts' => array_values($counts)];
    }

    /**
     * Status × category counts for a (filtered) example query; categories by
     * name, uncategorised last.
     *
     * @param  Builder<Example>  $query
     * @return Matrix
     */
    public static function matrix(Builder $query): array
    {
        $cells = $query->reorder()->toBase()
            ->selectRaw('category_id, status, COUNT(*) as aggregate')
            ->groupBy('category_id', 'status')
            ->get();

        $names = ExampleCategory::query()
            ->whereKey($cells->pluck('category_id')->filter()->unique()->all())
            ->pluck('name', 'id');

        $grouped = [];
        foreach ($cells as $cell) {
            $grouped[$cell->category_id ?? 0][$cell->status] = (int) $cell->aggregate;
        }

        uksort($grouped, function (int $a, int $b) use ($names): int {
            if ($a === 0 || $b === 0) {
                return $a === 0 ? 1 : -1;
            }

            return strcmp((string) $names[$a], (string) $names[$b]);
        });

        $rows = [];
        foreach ($grouped as $categoryId => $statusCounts) {
            $counts = self::fillStatuses(fn (string $status): int => $statusCounts[$status] ?? 0);
            $rows[] = [
                'category' => $categoryId === 0 ? __('example::pages.no_category') : (string) $names[$categoryId],
                'counts' => $counts,
                'total' => array_sum($counts),
            ];
        }

        $totals = self::fillStatuses(fn (string $status): int => (int) array_sum(array_column(array_column($rows, 'counts'), $status)));

        return ['rows' => $rows, 'totals' => $totals, 'total' => array_sum($totals)];
    }

    /**
     * @param  callable(string): int  $count
     * @return array<string, int>
     */
    private static function fillStatuses(callable $count): array
    {
        $filled = [];

        foreach (ExampleStatus::cases() as $status) {
            $filled[$status->value] = $count($status->value);
        }

        return $filled;
    }
}

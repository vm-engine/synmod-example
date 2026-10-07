<?php

declare(strict_types=1);

namespace VmEngine\Example\Imports;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleNotifier;
use VmEngine\Synapse\Models\ExcelImport;
use VmEngine\Synapse\Services\Excel\Contracts\ProcessesImport;
use VmEngine\Synapse\Services\Excel\Contracts\ReceivesImportTask;
use VmEngine\SynAuth\Models\User;

/**
 * Queued example import. Each row is validated like the editor; valid rows are
 * created as the importing user, invalid ones are skipped and listed (first
 * 50) in options.report. Per-row publish notifications are muted — the
 * import-finished notification is the summary.
 */
final class ExampleImportProcessor implements ProcessesImport, ReceivesImportTask
{
    /** Spreadsheet columns, in order. */
    public const COLUMNS = ['text', 'email', 'status', 'category', 'due_at', 'publish_note'];

    public const MAX_REPORTED = 50;

    private ?ExcelImport $task = null;

    public function setImportTask(ExcelImport $task): void
    {
        $this->task = $task;
    }

    public function process(Collection $rows, int $chunkIndex): void
    {
        $options = $this->task->options ?? [];
        $report = $options['report'] ?? ['imported' => 0, 'skipped' => 0, 'errors' => []];
        $firstRow = (int) ($options['starting_row'] ?? 1) + $chunkIndex * (int) ($options['chunk_size'] ?? $rows->count());
        $categories = ExampleCategory::query()->pluck('id', 'name')->mapWithKeys(fn (int $id, string $name): array => [mb_strtolower($name) => $id]);
        $user = $this->task?->user_id ? User::query()->find($this->task->user_id) : null;

        if ($user !== null) {
            Auth::setUser($user);
        }

        try {
            ExampleNotifier::muted(function () use ($rows, $firstRow, $categories, &$report): void {
                foreach ($rows->values() as $index => $row) {
                    $data = self::normalize($row, $categories->all());
                    $validator = Validator::make($data, self::rules(), [], self::attributes());

                    if ($validator->fails()) {
                        $report['skipped']++;
                        if (count($report['errors']) < self::MAX_REPORTED) {
                            $report['errors'][] = ['row' => $firstRow + $index, 'messages' => $validator->errors()->all()];
                        }

                        continue;
                    }

                    $valid = $validator->validated();
                    Example::query()->create([
                        'text' => $valid['text'],
                        'email' => $valid['email'],
                        'status' => $valid['status'],
                        'category_id' => $valid['category_id'] ?? null,
                        'due_at' => $valid['due_at'] ?? null,
                        'meta' => $valid['status'] === ExampleStatus::Published->value ? ['publish_note' => (string) $valid['publish_note']] : null,
                    ]);
                    $report['imported']++;
                }
            });
        } finally {
            if ($user !== null) {
                Auth::forgetUser();
            }
        }

        $this->task?->update(['options' => [...$options, 'report' => $report]]);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $categories  lower-cased name => id
     * @return array<string, mixed>
     */
    private static function normalize(array $row, array $categories): array
    {
        $text = fn (string $key): string => trim((string) ($row[$key] ?? ''));
        $due = $row['due_at'] ?? null;
        $category = $text('category');

        return [
            'text' => $text('text'),
            'email' => $text('email'),
            'status' => mb_strtolower($text('status')),
            // 0 = named but unknown → fails exists with a clear message.
            'category_id' => $category === '' ? null : ($categories[mb_strtolower($category)] ?? 0),
            'due_at' => $due instanceof DateTimeInterface ? $due->format('Y-m-d') : ($text('due_at') ?: null),
            'publish_note' => $text('publish_note') ?: null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'status' => ['required', Rule::enum(ExampleStatus::class)],
            'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            'due_at' => ['nullable', 'date_format:Y-m-d'],
            'publish_note' => ['nullable', 'string', 'max:500', 'required_if:status,published'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function attributes(): array
    {
        return ['text' => 'title', 'category_id' => 'category', 'due_at' => 'due date', 'publish_note' => 'publish note'];
    }
}

<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleSettings;

/**
 * Cover image (Example::$file) + multi-file attachments for an example.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait HasAttachments
{
    use DeletesAttachments;

    public ?TemporaryUploadedFile $cover = null;

    /** @var list<TemporaryUploadedFile> */
    public array $attachments = [];

    public function maxAttachments(): int
    {
        return ExampleSettings::maxAttachments();
    }

    /**
     * @return array<string, mixed>
     */
    protected function uploadRules(?Example $example): array
    {
        $remaining = $this->maxAttachments() - ($example?->attachments()->count() ?? 0);

        return [
            'cover' => ['nullable', 'image', 'max:2048'],
            'attachments' => ['array', 'max:'.min(5, max(0, $remaining))],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];
    }

    protected function storeUploads(Example $example): void
    {
        $disk = 'public';
        $directory = 'examples/'.$example->id;

        if ($this->cover !== null) {
            $old = $example->file;
            $example->update(['file' => $this->cover->store($directory, $disk)]);

            if ($old && $old !== $example->file) {
                Storage::disk($disk)->delete($old);
            }
        }

        $position = (int) $example->attachments()->max('position');

        foreach ($this->attachments as $upload) {
            $example->attachments()->create([
                'path' => $upload->store($directory, $disk),
                'original_name' => $upload->getClientOriginalName(),
                'mime' => (string) $upload->getMimeType(),
                'size' => (int) $upload->getSize(),
                'position' => ++$position,
            ]);
        }

        $this->reset('cover', 'attachments');
    }
}

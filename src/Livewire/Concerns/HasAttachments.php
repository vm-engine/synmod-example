<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;

/**
 * Cover image (Example::$file) + multi-file attachments for an example.
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait HasAttachments
{
    public const MAX_ATTACHMENTS = 10;

    public ?TemporaryUploadedFile $cover = null;

    /** @var list<TemporaryUploadedFile> */
    public array $attachments = [];

    /**
     * @return array<string, mixed>
     */
    protected function uploadRules(?Example $example): array
    {
        $remaining = self::MAX_ATTACHMENTS - ($example?->attachments()->count() ?? 0);

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

    public function deleteAttachment(string $token): void
    {
        if (! auth()->user()?->can('example.manage.update')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $id = ExampleAttachment::validateDeleteToken($token);
        $attachment = $id ? ExampleAttachment::query()->find($id) : null;

        if ($attachment === null) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::labels.invalid_delete_token'));

            return;
        }

        $attachment->delete();
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.attachment_deleted'));
    }
}

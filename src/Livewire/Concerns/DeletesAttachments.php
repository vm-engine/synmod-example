<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

use VmEngine\Example\Models\ExampleAttachment;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

/**
 * Token-checked attachment delete (needs example.manage.update).
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait DeletesAttachments
{
    use GuardsBackendPermission;

    public function deleteAttachment(string $token): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        if (! $this->guardAction('example.manage.update')) {
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

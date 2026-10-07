<?php

declare(strict_types=1);

namespace VmEngine\Example\Notifications;

use Illuminate\Notifications\Notification;
use VmEngine\Synapse\Notifications\Concerns\HasToastrFlag;

class ExampleImportFinished extends Notification
{
    use HasToastrFlag;

    public function __construct(
        public int $importId,
        public int $imported,
        public int $skipped,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'example::notifications.import_finished.title',
            'message' => 'example::notifications.import_finished.message',
            'params' => ['imported' => $this->imported, 'skipped' => $this->skipped],
            'icon' => 'ph ph-file-arrow-up',
            'url' => backend_route('example.integrations.import', ['task' => $this->importId]),
            'toastr' => $this->toastr ?? false,
        ];
    }
}

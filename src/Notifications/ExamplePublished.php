<?php

declare(strict_types=1);

namespace VmEngine\Example\Notifications;

use Illuminate\Notifications\Notification;
use VmEngine\Synapse\Notifications\Concerns\HasToastrFlag;

/**
 * One example published ($exampleId set) or a bulk publish ($count > 1).
 * Stores lang keys + params; the bell renders them in the viewer's locale.
 */
class ExamplePublished extends Notification
{
    use HasToastrFlag;

    public function __construct(
        public ?int $exampleId,
        public string $title = '',
        public int $count = 1,
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
        $single = $this->exampleId !== null;

        return [
            'title' => 'example::notifications.published.title',
            'message' => $single ? 'example::notifications.published.message' : 'example::notifications.published.bulk_message',
            'params' => ['title' => $this->title, 'count' => $this->count],
            'icon' => 'ph ph-megaphone',
            'url' => $single
                ? backend_route('example.show', ['id' => $this->exampleId])
                : backend_route('example.lists.inline-filters', ['filterStatus' => 'published']),
            'toastr' => $this->toastr ?? false,
        ];
    }
}

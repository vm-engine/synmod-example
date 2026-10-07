<?php

declare(strict_types=1);

namespace VmEngine\Example\Notifications;

use Illuminate\Notifications\Notification;
use VmEngine\Synapse\Notifications\Concerns\HasToastrFlag;

class ExampleDemoNotification extends Notification
{
    use HasToastrFlag;

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
            'title' => 'example::notifications.demo.title',
            'message' => 'example::notifications.demo.message',
            'params' => [],
            'icon' => 'ph ph-bell-ringing',
            'url' => backend_route('example.integrations.notifications'),
            'toastr' => $this->toastr ?? false,
        ];
    }
}

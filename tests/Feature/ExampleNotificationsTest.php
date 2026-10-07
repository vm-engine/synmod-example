<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Notifications\ExampleDemoNotification;
use VmEngine\Example\Notifications\ExamplePublished;
use VmEngine\Example\Support\ExampleNotifier;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['synapps.apps.notifications.enabled' => true]);
    Notification::fake();
    $this->role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $this->role->id]);
    $this->actor = User::factory()->create();
    $this->actor->roles()->attach($this->role);
    $this->otherAdmin = User::factory()->create();
    $this->otherAdmin->roles()->attach($this->role);
});

it('notifies admins except the actor when an example is published', function () {
    $this->actingAs($this->actor);
    $example = Example::factory()->draft()->create(['text' => 'Going live']);

    $example->update(['status' => 'published']);
    $example->update(['text' => 'Going live (edited)']);

    Notification::assertSentTo($this->otherAdmin, ExamplePublished::class, fn (ExamplePublished $n): bool => $n->toArray($this->otherAdmin)['params']['title'] === 'Going live');
    Notification::assertNotSentTo($this->actor, ExamplePublished::class);
    Notification::assertSentTimes(ExamplePublished::class, 1);
});

it('sends one summary for a bulk publish and nothing while muted', function () {
    $examples = Example::factory()->count(3)->draft()->create();
    $this->actingAs($this->actor);

    Livewire::test('example::lists.bulk-actions')
        ->set('selected', $examples->pluck('id')->all())
        ->call('bulkSetStatus', 'published');

    Notification::assertSentTo($this->otherAdmin, ExamplePublished::class, fn (ExamplePublished $n): bool => $n->count === 3);

    ExampleNotifier::muted(fn () => Example::factory()->published()->create());
    Notification::assertSentTimes(ExamplePublished::class, 1);
});

it('sends a test notification to the current user only', function () {
    Livewire::actingAs($this->actor)->test('example::integrations.notifications')
        ->set('withToast', true)
        ->call('sendTest')
        ->assertDispatched('notify', variant: 'success');

    Notification::assertSentTo($this->actor, ExampleDemoNotification::class, fn ($n): bool => $n->toastr === true);
    Notification::assertNotSentTo($this->otherAdmin, ExampleDemoNotification::class);
});

it('stays silent when notifications are disabled', function () {
    config(['synapps.apps.notifications.enabled' => false]);
    $this->actingAs($this->actor);

    Example::factory()->published()->create();

    Notification::assertNothingSent();
});

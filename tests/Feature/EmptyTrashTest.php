<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;
use VmEngine\SynAuth\Models\UserActivity;
use VmEngine\SynAuth\Services\OtpProtectionService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    Example::factory()->count(2)->create()->each->delete();
    $this->kept = Example::factory()->create();
});

it('asks for an OTP before emptying the trash', function () {
    Livewire::actingAs($this->user)->test('example::lists.trashed')
        ->call('emptyTrash')
        ->assertDispatched('request-otp-verification', purpose: 'example-empty-trash');

    expect(Example::onlyTrashed()->count())->toBe(2);
});

it('empties the trash after a valid token and logs it', function () {
    $token = app(OtpProtectionService::class)->generateActionToken($this->user, 'example-empty-trash');

    Livewire::actingAs($this->user)->test('example::lists.trashed')
        ->call('handleOtpVerified', $token, 'example-empty-trash')
        ->assertDispatched('notify', variant: 'success');

    expect(Example::withTrashed()->pluck('id')->all())->toBe([$this->kept->id])
        ->and(UserActivity::query()->where('action', 'example.trash_emptied')->exists())->toBeTrue();
});

it('deletes nothing for a bad token, a reused token or another purpose', function () {
    $service = app(OtpProtectionService::class);
    $wrongPurpose = $service->generateActionToken($this->user, 'something-else');
    $used = $service->generateActionToken($this->user, 'example-empty-trash');
    $service->validateActionToken($used, $this->user->id);

    Livewire::actingAs($this->user)->test('example::lists.trashed')
        ->call('handleOtpVerified', 'garbage', 'example-empty-trash')
        ->assertDispatched('notify', variant: 'danger')
        ->call('handleOtpVerified', $used, 'example-empty-trash')
        ->call('handleOtpVerified', $wrongPurpose, 'example-empty-trash')
        ->call('handleOtpVerified', $wrongPurpose, 'something-else');

    expect(Example::onlyTrashed()->count())->toBe(2);
});

it('denies users without delete permission and says when the trash is empty', function () {
    $readOnly = User::factory()->create();
    $readOnly->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    Livewire::actingAs($readOnly)->test('example::lists.trashed')
        ->call('emptyTrash')
        ->assertNotDispatched('request-otp-verification');

    Example::onlyTrashed()->forceDelete();

    Livewire::actingAs($this->user)->test('example::lists.trashed')
        ->call('emptyTrash')
        ->assertDispatched('notify', variant: 'info')
        ->assertNotDispatched('request-otp-verification');
});

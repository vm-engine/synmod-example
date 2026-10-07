<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    $this->example = Example::factory()->draft()->create(['file' => null]); // draft: a published example would also need a publish note to save
});

it('stores a cover image and replaces the previous one', function () {
    $component = Livewire::actingAs($this->user)->test('example::example-editor', ['id' => $this->example->id]);

    $component->set('cover', UploadedFile::fake()->image('one.jpg'))->call('save')->assertHasNoErrors();
    $first = $this->example->refresh()->file;
    Storage::disk('public')->assertExists($first);

    $component->set('cover', UploadedFile::fake()->image('two.jpg'))->call('save')->assertHasNoErrors();
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($this->example->refresh()->file);
});

it('stores attachments and deletes one with its token', function () {
    $component = Livewire::actingAs($this->user)->test('example::example-editor', ['id' => $this->example->id])
        ->set('attachments', [UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'), UploadedFile::fake()->image('b.png')])
        ->call('save')
        ->assertHasNoErrors();

    expect($this->example->attachments()->count())->toBe(2);

    $attachment = $this->example->attachments()->first();
    $component->call('deleteAttachment', $attachment->delete_token);

    expect(ExampleAttachment::query()->find($attachment->id))->toBeNull();
    Storage::disk('public')->assertMissing($attachment->path);
});

it('rejects disallowed files and oversized covers', function () {
    Livewire::actingAs($this->user)->test('example::example-editor', ['id' => $this->example->id])
        ->set('attachments', [UploadedFile::fake()->create('x.exe', 10)])
        ->set('cover', UploadedFile::fake()->image('big.jpg')->size(3000))
        ->call('save')
        ->assertHasErrors(['attachments.0' => 'mimes', 'cover' => 'max']);
});

it('caps attachments per example', function () {
    ExampleAttachment::factory()->count(9)->for($this->example)->create();

    Livewire::actingAs($this->user)->test('example::example-editor', ['id' => $this->example->id])
        ->set('attachments', [UploadedFile::fake()->image('1.png'), UploadedFile::fake()->image('2.png')])
        ->call('save')
        ->assertHasErrors(['attachments' => 'max']);
});

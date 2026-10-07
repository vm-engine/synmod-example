<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    foreach (['manage', 'category'] as $feature) {
        RolePermission::factory()->forModule('example', $feature)->fullCrud()->create(['role_id' => $role->id]);
    }
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    $this->token = $this->user->createToken('api-test')->plainTextToken;
});

function apiV1(string $method, string $uri, array $data = [], ?string $token = null)
{
    return test()->withHeaders(['Authorization' => 'Bearer '.($token ?? test()->token), 'Accept' => 'application/json'])
        ->json($method, '/api/v1/example'.$uri, $data);
}

it('lists examples with filters and a capped page size', function () {
    $news = ExampleCategory::factory()->create(['name' => 'News']);
    Example::factory()->count(3)->draft()->create(['category_id' => $news->id]);
    Example::factory()->published()->create(['text' => 'Shipped thing']);

    apiV1('GET', '/examples')->assertOk()->assertJsonCount(4, 'data')->assertJsonPath('meta.total', 4);
    apiV1('GET', '/examples?status=published')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.text', 'Shipped thing');
    apiV1('GET', '/examples?category_id='.$news->id)->assertJsonCount(3, 'data');
    apiV1('GET', '/examples?q=Shipped')->assertJsonCount(1, 'data');
    apiV1('GET', '/examples?per_page=500')->assertStatus(422)->assertJsonValidationErrors('per_page');
    apiV1('GET', '/examples?status=archived')->assertStatus(422);
});

it('shows one example and 404s on a missing one', function () {
    $example = Example::factory()->create(['text' => 'Detail me']);

    apiV1('GET', '/examples/'.$example->id)->assertOk()
        ->assertJsonPath('data.text', 'Detail me')
        ->assertJsonStructure(['data' => ['id', 'text', 'slug', 'status', 'status_label', 'category', 'tags', 'due_at', 'email', 'created_at', 'updated_at']]);
    apiV1('GET', '/examples/999999')->assertNotFound();
});

it('lists categories', function () {
    ExampleCategory::factory()->create(['name' => 'Zeta']);
    ExampleCategory::factory()->create(['name' => 'Alpha']);

    apiV1('GET', '/categories')->assertOk()->assertJsonPath('data.0.name', 'Alpha');
});

it('updates the status and requires a publish note to publish', function () {
    $example = Example::factory()->draft()->create();

    apiV1('PATCH', "/examples/{$example->id}/status", ['status' => 'review'])->assertOk()->assertJsonPath('data.status', 'review');
    apiV1('PATCH', "/examples/{$example->id}/status", ['status' => 'published'])->assertStatus(422)->assertJsonValidationErrors('publish_note');
    apiV1('PATCH', "/examples/{$example->id}/status", ['status' => 'published', 'publish_note' => 'Via API'])->assertOk();

    expect($example->refresh()->meta)->toMatchArray(['publish_note' => 'Via API']);
});

it('rejects missing tokens and missing permissions', function () {
    $this->withHeaders(['Accept' => 'application/json'])->getJson('/api/v1/example/examples')->assertUnauthorized();

    $reader = User::factory()->create();
    $reader->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());
    $readerToken = $reader->createToken('reader')->plainTextToken;
    $example = Example::factory()->create();

    apiV1('GET', '/examples', [], $readerToken)->assertOk();
    apiV1('GET', '/categories', [], $readerToken)->assertForbidden();
    apiV1('PATCH', "/examples/{$example->id}/status", ['status' => 'review'], $readerToken)->assertForbidden();
});

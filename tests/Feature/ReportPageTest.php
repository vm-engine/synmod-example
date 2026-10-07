<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Models\ExcelExport;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('shows the matrix for the filtered examples and pushes chart data on filter change', function () {
    $news = ExampleCategory::factory()->create(['name' => 'News']);
    $blog = ExampleCategory::factory()->create(['name' => 'Blog']);
    Example::factory()->count(2)->draft()->create(['category_id' => $news->id]);
    Example::factory()->published()->create(['category_id' => $blog->id]);

    Livewire::actingAs($this->user)
        ->test('example::pages.report')
        ->assertSeeHtml('<td><p>News</p></td>')
        ->assertSeeHtml('<td><p>Blog</p></td>')
        ->set('filterCategory', (string) $news->id)
        ->assertDispatched('example-chart-report')
        ->assertSeeHtml('<td><p>News</p></td>')
        ->assertDontSeeHtml('<td><p>Blog</p></td>');
});

it('queues an export with the current filters and opens the progress page', function () {
    Queue::fake();

    Livewire::actingAs($this->user)
        ->test('example::pages.report')
        ->set('dueFrom', '2026-10-01')
        ->call('exportReport')
        ->assertRedirect();

    $task = ExcelExport::query()->sole();

    expect($task->reference)->toBe('example-report-'.$this->user->id)
        ->and($task->user_id)->toBe($this->user->id);
});

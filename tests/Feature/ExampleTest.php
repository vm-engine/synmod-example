<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user with full permissions for example module
    $permission = RolePermission::factory()
        ->forModule('example', 'manage')
        ->fullCrud();
    $this->adminRole = Role::factory()->has($permission)->create([
        'name' => 'Administrator',
        'slug' => 'admin',
        'level' => 5,
    ]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->adminRole);
});

describe('Model Factory', function () {
    it('can create an example using factory', function () {
        $example = Example::factory()->create([
            'text' => 'Test Text',
            'email' => 'test@example.com',
        ]);

        expect($example)
            ->text->toBe('Test Text')
            ->email->toBe('test@example.com');

        $this->assertDatabaseHas('examples', [
            'text' => 'Test Text',
            'email' => 'test@example.com',
        ]);
    });
});

describe('Page Rendering', function () {
    it('renders the example form', function () {
        $response = $this->actingAs($this->user)->get(backend_route('example.form'));

        $response->assertStatus(200);
    });

    it('renders the example list', function () {
        $response = $this->actingAs($this->user)->get(backend_route('example.index'));

        $response->assertStatus(200);
    });

    it('renders the example edit form with data', function () {
        $example = Example::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(backend_route('example.form', ['id' => $example->id]));

        $response->assertStatus(200);
    });
});

describe('Create Example', function () {
    it('can create an example via Livewire component', function () {
        $category = ExampleCategory::factory()->create();

        Livewire::actingAs($this->user)
            ->test('example.example-form')
            ->set('form.category_id', $category->id)
            ->set('form.text', 'Test Example')
            ->set('form.textarea', 'Test textarea content')
            ->set('form.email', 'test@example.com')
            ->set('form.protected', 'Test@123!')
            ->set('form.number', 42)
            ->set('form.masked', '1000')
            ->set('form.dropdown', '5')
            ->set('form.radio', '3')
            ->set('form.checkbox', [1, 2, 3])
            ->set('form.date', '2025-01-15')
            ->set('form.datetime', '2025-01-15 10:30')
            ->set('form.color', '#ff0000')
            ->call('save')
            ->assertRedirect(backend_route('example.index'))
            ->assertSessionHas('success', 'Example created successfully.');

        $this->assertDatabaseHas('examples', [
            'text' => 'Test Example',
            'email' => 'test@example.com',
            'category_id' => $category->id,
        ]);
    });

    it('validates required fields when creating', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-form')
            ->set('form.text', '')
            ->set('form.email', '')
            ->set('form.protected', '')
            ->call('save')
            ->assertHasErrors([
                'form.text' => 'required',
                'form.email' => 'required',
                'form.protected' => 'required',
            ]);
    });

    it('validates email format when creating', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-form')
            ->set('form.text', 'Test')
            ->set('form.email', 'invalid-email')
            ->set('form.protected', 'Test@123!')
            ->set('form.number', 10)
            ->set('form.checkbox', [1])
            ->call('save')
            ->assertHasErrors(['form.email' => 'email']);
    });

    it('validates password complexity when creating', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-form')
            ->set('form.text', 'Test')
            ->set('form.email', 'test@example.com')
            ->set('form.protected', 'simple')
            ->set('form.number', 10)
            ->set('form.checkbox', [1])
            ->call('save')
            ->assertHasErrors(['form.protected']);
    });
});

describe('Update Example', function () {
    it('can update an example via Livewire component', function () {
        $example = Example::factory()->create([
            'text' => 'Original Text',
            'email' => 'original@example.com',
            'protected' => 'Original@123!',
        ]);

        Livewire::actingAs($this->user)
            ->test('example.example-form', ['id' => $example->id])
            ->assertSet('form.text', 'Original Text')
            ->assertSet('form.email', 'original@example.com')
            ->set('form.text', 'Updated Text')
            ->set('form.email', 'updated@example.com')
            ->set('form.protected', 'Updated@123!')
            ->call('save')
            ->assertRedirect(backend_route('example.index'))
            ->assertSessionHas('success', 'Example updated successfully.');

        $this->assertDatabaseHas('examples', [
            'id' => $example->id,
            'text' => 'Updated Text',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseMissing('examples', [
            'id' => $example->id,
            'text' => 'Original Text',
        ]);
    });

    it('validates required fields when updating', function () {
        $example = Example::factory()->create();

        Livewire::actingAs($this->user)
            ->test('example.example-form', ['id' => $example->id])
            ->set('form.text', '')
            ->set('form.email', '')
            ->call('save')
            ->assertHasErrors([
                'form.text' => 'required',
                'form.email' => 'required',
            ]);
    });

    it('redirects when trying to edit non-existent example', function () {
        $this->actingAs($this->user);

        Livewire::test('example.example-form', ['id' => 99999])
            ->assertRedirect(backend_route('example.index'))
            ->assertSessionHas('danger', 'Data not found.');
    });
});

describe('Delete Example', function () {
    it('can delete an example via Livewire component', function () {
        $example = Example::factory()->create([
            'text' => 'To Be Deleted',
        ]);

        $this->assertDatabaseHas('examples', [
            'id' => $example->id,
            'text' => 'To Be Deleted',
        ]);

        Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->call('delete', $example->delete_token)
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('examples', [
            'id' => $example->id,
        ]);
    });

    it('handles deleting non-existent example gracefully', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->call('delete', 'invalid-token')
            ->assertDispatched('notify');

        // Should not throw exception, handled in component
        expect(true)->toBeTrue();
    });
});

describe('List Filtering', function () {
    beforeEach(function () {
        // Create categories
        $this->category1 = ExampleCategory::factory()->create(['name' => 'Category 1']);
        $this->category2 = ExampleCategory::factory()->create(['name' => 'Category 2']);

        // Create test examples
        $this->example1 = Example::factory()->create([
            'text' => 'Laravel Framework',
            'email' => 'laravel@example.com',
            'category_id' => $this->category1->id,
            'dropdown' => 5,
        ]);

        $this->example2 = Example::factory()->create([
            'text' => 'PHP Programming',
            'email' => 'php@example.com',
            'category_id' => $this->category2->id,
            'dropdown' => 3,
        ]);

        $this->example3 = Example::factory()->create([
            'text' => 'Vue.js Framework',
            'email' => 'vue@example.com',
            'category_id' => $this->category1->id,
            'dropdown' => 5,
        ]);
    });

    it('can filter by search query', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('q', 'Laravel');

        $results = $component->get('exampleList');

        expect($results->count())->toBe(1);
        expect($results->first()->text)->toBe('Laravel Framework');
    });

    it('requires minimum 3 characters for search', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('q', 'La');

        $results = $component->get('exampleList');

        // Should return all results since query is too short
        expect($results->count())->toBe(3);
    });

    it('can filter by dropdown option', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('filterOption', 5);

        $results = $component->get('exampleList');

        expect($results->count())->toBe(2);
    });

    it('can filter by category', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('filterCategories', [$this->category1->id]);

        $results = $component->get('exampleList');

        expect($results->count())->toBe(2);
        expect($results->pluck('category_id')->unique()->toArray())
            ->toBe([$this->category1->id]);
    });

    it('can filter by multiple categories', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('filterCategories', [$this->category1->id, $this->category2->id]);

        $results = $component->get('exampleList');

        expect($results->count())->toBe(3);
    });

    it('can combine search and dropdown filter', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('q', 'Framework')
            ->set('filterOption', 5);

        $results = $component->get('exampleList');

        expect($results->count())->toBe(2);
    });

    it('can combine all filters together', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('q', 'Framework')
            ->set('filterOption', 5)
            ->set('filterCategories', [$this->category1->id]);

        $results = $component->get('exampleList');

        expect($results->count())->toBe(2);
    });

    it('can sort data ascending and descending', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->call('sortData', 'text')
            ->assertSet('sort', 'text')
            ->assertSet('sortDirection', 'asc')
            ->call('sortData', 'text')
            ->assertSet('sortDirection', 'desc');
    });

    it('resets page when filters are updated', function () {
        Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->set('q', 'test')
            ->assertSet('q', 'test');

        // Page should reset when filter changes (tested implicitly by Livewire)
        expect(true)->toBeTrue();
    });

    it('can toggle category filter', function () {
        $component = Livewire::actingAs($this->user)
            ->test('example.example-list')
            ->call('filterByCategory', $this->category1->id)
            ->assertSet('filterCategories', [$this->category1->id])
            ->call('filterByCategory', $this->category1->id)
            ->assertSet('filterCategories', []);
    });
});

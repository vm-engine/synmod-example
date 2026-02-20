# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Project Overview

This is the **Example Module** (`vm-engine/example`), a **package module** for Laravel Synapse architecture. It demonstrates the modular architecture patterns used in Synapse projects and serves as a reference implementation.

**Type:** Composer package module (lives in `packages/synmod-example/`)
**Package Name:** `vm-engine/example`
**Module ID:** `example`
**URL Prefix:** `/example/*`

## Architecture

### Namespace Structure

This is a **package module** that uses the **VmEngine vendor namespace** (not SynApps):

- **Application:** `VmEngine\Example\` → `src/`
- **Factories:** `VmEngine\Example\Factories\` → `database/factories/`
- **Seeders:** `VmEngine\Example\Seeders\` → `database/seeders/`

These namespaces are defined in `composer.json` autoload section.

**Note:** Directory modules (in `synapps/modules/`) use `SynApps\Modules\{ModuleName}\` namespace, while package modules use their vendor namespace (in this case `VmEngine\Example\`).

### Service Provider Registration

Livewire components are auto-registered via `AutoRegistersComponents` trait using Livewire v4's `addNamespace()`:

```php
// In ExampleServiceProvider::register()
$this->registerComponents(); // auto-discovers all Livewire components
```

Components are referenced with namespace notation:
```blade
<livewire:example::button-sample />
<livewire:example::category-form />
```

### ACL (Access Control)

The module implements a hierarchical permission structure in `module.json`:

- **Parent permission:** `example.manage` (with CRUD sub-permissions)
- **Sub-permission:** `example.manage.subfeature` (nested under manage)
- **Separate permission:** `example.category` (independent CRUD)

Routes use `can-access` middleware with pipe-delimited permissions:
```php
->middleware('can-access:example.manage.create|example.manage.update')
```

### Navigation Structure

Defined in `module.json`:
- **Backend nav:** Multi-level menu with icons, ACL checks, and route references
- Uses translation keys: `__example::menu.be.parent`
- Icon: Font Awesome classes (`fa-solid fa-table`)

### Route Organization

- `routes/web.backend.php` → Backend admin routes (auto-prefixed `/admin/example/*`, auto-authenticated)
- `routes/web.php` → Frontend routes (currently empty, would use `url_prefix` from module.json)

### Livewire Patterns

**Form Objects Pattern:**
Uses dedicated Form classes (`Livewire\Form`) to encapsulate validation and data logic:
- `ExampleFormObject` in `src/Livewire/Forms/`
- Contains `setExample()`, `store()`, `update()`, and `rules()` methods

**URL State Management:**
List components use `#[Url()]` attribute for filter persistence:
```php
#[Url()] public $q;
#[Url()] public $filterCategories = [];
```

**Computed Properties:**
List queries use `#[Computed()]` for query optimization

**Event Dispatching:**
Uses v3 syntax: `$this->dispatch('notify', variant: 'success', ...)`

### Model Patterns

**Query Scopes:**
Uses Laravel 11+ attribute syntax:
```php
#[Scope]
protected function search(Builder $builder, string $q): void
```

**Factory Registration:**
Override `newFactory()` method to use custom namespace:
```php
protected static function newFactory()
{
    return ExampleFactory::new();
}
```

### Localization

Translation files in `lang/en/`:
- `acl.php` → Permission labels
- `menu.php` → Navigation labels
- `labels.php` → Form/UI labels

Reference in code: `__example::menu.be.parent`
Reference in views: `@lang('example::labels.field_name')`

## Development Commands

**Testing (from parent Laravel project):**
```bash
# Run all tests
./vendor/bin/pest

# Run this module's tests specifically
./vendor/bin/pest packages/synmod-example/tests

# Filter specific test
./vendor/bin/pest --filter="can create an example"
```

**Code Quality:**
```bash
# Fix code style
./vendor/bin/pint packages/synmod-example
```

**Development:**
This module is developed within a parent Laravel Synapse project. Changes here should be tested in the parent project context.

## Testing Notes

- Uses `RefreshDatabase` trait for isolated test database
- ACL testing: Create permissions via `RolePermission::factory()->forModule('example', 'manage')->fullCrud()`
- Tests authenticate users with roles: `$this->actingAs($user)`
- Route testing uses named routes: `route('backend.example.index')`

## Key Files

- `module.json` → Module configuration, ACL, navigation, dependencies
- `src/ExampleServiceProvider.php` → Livewire component registration
- `src/Models/Example.php` → Main model with custom factory, search scope, category relationship
- `src/Livewire/Forms/ExampleFormObject.php` → Form validation and persistence logic
- `composer.json` → Namespace definitions (critical for PSR-4 autoloading)

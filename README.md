# Example Module

A reference implementation demonstrating Laravel Synapse modular architecture patterns.

## Overview

This module serves as an example and template for building package modules in Laravel Synapse projects. It showcases best practices for:

- Modular architecture with proper namespace organization
- Livewire v4 MFC/SFC (Multi-File and Single-File Component) patterns
- ACL (Access Control List) implementation with hierarchical permissions
- Backend navigation with multi-level menus
- Model factories and seeders
- Testing with Pest
- Session-based filter/sort state persistence

## Installation

### 1. Add Repository URL

Add the repository to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/vm-engine/synmod-example"
        }
    ]
}
```

### 2. Install Package

```bash
composer require vm-engine/synmod-example
```

### 3. Discover Modules and Cache Configuration

```bash
php artisan synapps:discover
php artisan config:cache
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Build Assets

```bash
npm run build
```

## Features

- **Example Management**: CRUD operations for example records with correct browser page titles
- **Category Management**: Organize examples by categories
- **Advanced Filtering**: Search, filter by dropdown options and categories with session-based state persistence
- **File Uploads**: Handle document uploads (PDF, DOC, XLS, PPT)
- **Form Validation**: Comprehensive validation including password rules
- **ACL Integration**: Fine-grained permission controls

## Module Structure

```
packages/synmod-example/
├── src/
│   ├── ExampleServiceProvider.php          # Auto-registers components via AutoRegistersComponents
│   └── Models/
│       ├── Example.php
│       └── ExampleCategory.php
├── resources/views/components/             # MFC Livewire components (Livewire v4)
│   ├── ⚡example-list/
│   │   ├── example-list.php
│   │   └── example-list.blade.php
│   ├── ⚡example-form/
│   │   ├── example-form.php
│   │   └── example-form.blade.php
│   ├── ⚡category-list/
│   │   ├── category-list.php
│   │   └── category-list.blade.php
│   ├── ⚡category-form/
│   │   ├── category-form.php
│   │   └── category-form.blade.php
│   └── ⚡example-page/
│       ├── example-page.php
│       └── example-page.blade.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.backend.php
│   └── web.php
├── lang/en/
├── tests/
├── module.json
└── composer.json
```

> **Note:** This module uses the MFC (Multi-File Component) format introduced in Synapse v2.0. Components live in `resources/views/components/` rather than `src/Livewire/`.

## Namespace Structure

This module uses the **VmEngine vendor namespace**:

- Application: `VmEngine\Example\`
- Factories: `VmEngine\Example\Factories\`
- Seeders: `VmEngine\Example\Seeders\`

## Permissions

The module defines the following permissions in `module.json`:

### Example Management (`example.manage`)
- Create, Read, Update, Delete
- Sub-feature: `example.manage.subfeature` (nested permissions)

### Category Management (`example.category`)
- Create, Read, Update, Delete

## Routes

All backend routes are automatically prefixed with `/admin/example/` and require authentication:

- `GET /admin/example/` - Example list
- `GET /admin/example/form/{id?}` - Create/edit form
- `GET /admin/example/category` - Category management

## Development

### Running Tests

**IMPORTANT:** Tests for this module **must be run from the parent Laravel project**, not from within the module directory itself.

From the parent Laravel project root:

```bash
# Run this module's tests (use packages path for local development)
php vendor/bin/pest packages/synmod-example/tests

# Run specific test
php vendor/bin/pest --filter="can create an example"
```

**Note:** When the module is released via Composer, use the `vendor/vm-engine/synmod-example/tests` path instead.

### Code Quality

```bash
# Fix code style (from project root)
php vendor/bin/pint packages/synmod-example/

# PHPStan analysis (from project root)
php vendor/bin/phpstan analyse --configuration=packages/synmod-example/phpstan.neon
```

## Key Patterns

### MFC Component Format

Components use Livewire v4's Multi-File Component format with anonymous classes:

```php
// resources/views/components/⚡example-list/example-list.php
<?php

use Livewire\Component;

new class extends Component {
    // component logic

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
```

```blade
{{-- resources/views/components/⚡example-list/example-list.blade.php --}}
<div>
    {{-- component view --}}
</div>
```

### Page Titles

Full-page MFC components set browser `<title>` in `render()`:

```php
public function render()
{
    return $this->view()->title(page_title($this->title()));
}
```

### Namespace Notation

All Livewire component references use namespace notation (Synapse v2.2+):

```blade
{{-- In Blade views --}}
<livewire:example::example-list />
<livewire:example::category-list />
```

```php
// In route files
Route::livewire('/example', 'example::example-list');
```

### URL State Management

Filters persist in URL for shareable states:

```php
#[Url()] public string $q = '';
#[Url()] public array $filterCategories = [];
```

### Component Auto-Registration

Components are auto-registered via the `AutoRegistersComponents` trait — no manual registration needed:

```php
class ExampleServiceProvider extends ServiceProvider
{
    use AutoRegistersComponents;

    public function register(): void
    {
        $this->registerComponents();
    }
}
```

### Query Scopes (Laravel 12+)

Uses attribute syntax for cleaner scope definitions:

```php
#[Scope]
protected function search(Builder $builder, string $q): void
{
    $builder->whereAny(['text', 'textarea', 'email'], 'like', "%{$q}%");
}
```

## Requirements

- Laravel 12
- Livewire v4
- vm-engine/synapse ^2.2
- vm-engine/synapps-auth ^2.0

## License

This module is part of the Synapse Engine package suite.

## Author

**Achmad Ardiansyah**
Email: theadods@gmail.com

## Version

2.0.2

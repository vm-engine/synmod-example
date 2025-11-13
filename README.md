# Example Module

A reference implementation demonstrating Laravel Synapse modular architecture patterns.

## Overview

This module serves as an example and template for building package modules in Laravel Synapse projects. It showcases best practices for:

- Modular architecture with proper namespace organization
- Livewire v3 components with Form Objects pattern
- ACL (Access Control List) implementation with hierarchical permissions
- Backend navigation with multi-level menus
- Model factories and seeders
- Testing with Pest

## Installation

Add to your Laravel Synapse project via Composer:

```bash
composer require vm-engine/example
```

Run migrations:

```bash
php artisan migrate
```

## Features

- **Example Management**: CRUD operations for example records
- **Category Management**: Organize examples by categories
- **Advanced Filtering**: Search, filter by dropdown options and categories
- **File Uploads**: Handle document uploads (PDF, DOC, XLS, PPT)
- **Form Validation**: Comprehensive validation including password rules
- **ACL Integration**: Fine-grained permission controls

## Module Structure

```
packages/synmod-example/
├── src/
│   ├── ExampleServiceProvider.php
│   ├── Models/
│   │   ├── Example.php
│   │   └── ExampleCategory.php
│   └── Livewire/
│       ├── ExampleList.php
│       ├── ExampleForm.php
│       ├── CategoryList.php
│       ├── CategoryForm.php
│       └── Forms/
│           ├── ExampleFormObject.php
│           └── CategoryFormObject.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.backend.php
│   └── web.php
├── resources/views/
├── lang/en/
├── tests/
├── module.json
└── composer.json
```

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

From the parent Laravel project:

```bash
# Run all tests
./vendor/bin/pest

# Run this module's tests
./vendor/bin/pest packages/synmod-example/tests

# Run specific test
./vendor/bin/pest --filter="can create an example"
```

### Code Quality

```bash
# Fix code style
./vendor/bin/pint packages/synmod-example
```

## Key Patterns

### Livewire Form Objects

This module demonstrates the Form Objects pattern for cleaner component code:

```php
class ExampleFormObject extends Form
{
    public function setExample(Example $example): void { }
    public function store(): void { }
    public function update(): void { }
    public function rules(): array { }
}
```

### URL State Management

Filters persist in URL for shareable states:

```php
#[Url()] public $q;
#[Url()] public $filterCategories = [];
```

### Query Scopes (Laravel 11+)

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
- Livewire v3
- Laravel Synapse (vm-engine/synapse)

## License

This module is part of the Synapse Engine package suite.

## Author

**Achmad Ardiansyah**
Email: theadods@gmail.com

## Version

0.1.0

# Example Module

A reference implementation of Laravel Synapse modular architecture patterns — and, since 3.0, a **Pattern Catalog** of 57 working examples you can open, copy and adapt.

## Overview

Open **Example → Pattern Catalog** (`/apps/example/catalog`) in the backend. Every card links to a live page and lists the source files behind it, grouped as:

- **Lists** — table, card grid, bulk actions, trash & restore, drag-sortable rows, tree, grouped, expandable, load more, inline edit, inline filters, Excel export.
- **Forms** — full editor with sticky action bar and error summary, modal/drawer/tabbed forms, modal and page wizards, repeater, uploads, rich text, remote/creatable select, conditional fields, real-time validation, slug, JSON, color picker.
- **Pages** — dashboard (ApexCharts), tabbed settings (DbConfig), report + print view, kanban, calendar, split pane, empty states, live progress, detail page with tabs.
- **Components** — a gallery of the synapse UI components.
- **Integrations** — auth directives + helpers, activity log, OTP-protected action, conditional (ABAC) permission, guarded actions, user field extension, remembered filters, API v1 + HMAC signing (Postman collection in `docs/postman/`), notifications, global search, queued Excel import, public frontend pages.

## Requirements

- PHP 8.2+, Laravel 11–13, Livewire 4
- `vm-engine/synapse` ^3.3 and `vm-engine/synapps-auth` ^3.1
- `mews/purifier` ^3.4; npm `jodit` and `apexcharts` (added to the host `package.json` by `synapps:discover`)

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

### 3. Discover Modules and Compile Configuration

```bash
php artisan synapps:discover
php artisan synapps:config
```

### 4. Run Migrations and Setup

```bash
php artisan migrate
php artisan example:setup     # adds the module JS import, creates the Example author demo role, indexes examples for global search
```

Optional demo data: the module's seeders (categories, tags, 60 examples, a node tree, the Example author role).

### 5. Build Assets

```bash
npm install
npm run build
```

### Optional platform features

- **Global search** (Ctrl/⌘+K): `php artisan synapps:searchable-setup`, then `php artisan example:search-reindex`.
- **Notifications** (bell + toast): `php artisan synotif:setup`.
- **Queued export/import**: run a queue worker (`php artisan queue:work`).
- **API**: create an API user and token under Auth → API Users; generate a signing secret for the signed `POST /examples`.

## Module Structure

```
packages/synmod-example/
├── src/
│   ├── Catalog/PatternCatalog.php          # registry behind the Pattern Catalog page
│   ├── Console/                            # example:setup, example:search-reindex
│   ├── Exports/ Imports/                   # Excel export query, import processor
│   ├── Http/                               # API v1 controller, resources, requests
│   ├── Livewire/ (Concerns, Forms)         # shared traits and form objects
│   ├── Models/ Observers/ Notifications/ Listeners/
│   ├── Support/                            # settings, stats, activity, search index, notifier…
│   └── View/Components/                    # <x-example::chart>, <x-example::rich-text>
├── resources/views/components/             # MFC Livewire components, one folder per page
│   ├── lists/ forms/ pages/ integrations/ frontend/
│   └── example-list/ example-editor/ …
├── routes/
│   ├── web.backend.php                     # /apps/example/*
│   ├── web.php                             # public /example/*
│   └── api.v1.php                          # /api/v1/example/*
├── database/ (migrations, factories, seeders)
├── docs/postman/                           # API v1 Postman collection
├── lang/en/ tests/ module.json composer.json
```

## Namespace Structure

This module uses the **VmEngine vendor namespace**:

- Application: `VmEngine\Example\`
- Factories: `VmEngine\Example\Factories\`
- Seeders: `VmEngine\Example\Seeders\`

## Permissions

Defined in `module.json` (assign them to roles under Auth → Roles):

| Permission | Actions | Covers |
|---|---|---|
| `example.manage` | create, read, update, delete | examples and most pattern pages |
| `example.category` | create, read, update, delete | categories |
| `example.tag` | create, read, update, delete | tags |
| `example.node` | create, read, update, delete | node tree and node browser |
| `example.settings` | read, update | module settings |

The **Example author** role (from `example:setup`) demonstrates an ABAC condition: update/delete only on examples the user created.

## Routes

- Backend: `/apps/example/…` (authenticated, per-route `can-access` permissions) — start at `/apps/example/catalog`.
- Frontend: `/example`, `/example/search`, `/example/{slug}` (public, published examples only).
- API: `/api/v1/example/examples`, `/examples/{id}`, `/examples/{id}/status`, `/categories`, signed `POST /examples` — see the API page in the catalog.

## Upgrading from 2.x

- Requires synapse ^3.3 and synapps-auth ^3.1 (and their own upgrade steps: copy resources, rebuild assets).
- Run the new migrations — they add showcase columns to `examples`, new tag/node/attachment tables, and a nullable `example_default_category_id` on `users`.
- Run `php artisan example:setup` and rebuild assets.
- Assign the new `example.tag`, `example.node` and `example.settings` permissions to your roles.
- Deleting an example is now a soft delete (Trash page); old `<x-synapse-select>` usages were replaced by `<x-synapse-adv-select>`.

## Development

### Running Tests

**IMPORTANT:** Tests for this module **must be run from the parent Laravel project**, not from within the module directory itself.

From the parent Laravel project root:

```bash
# Run this module's tests (use packages path for local development)
php vendor/bin/pest packages/synmod-example/tests --parallel

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

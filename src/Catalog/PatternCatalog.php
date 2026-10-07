<?php

declare(strict_types=1);

namespace VmEngine\Example\Catalog;

/**
 * Static index of every UI / integration pattern the example module demonstrates
 * (or will). `built` entries must point at a real backend route name (without
 * the `backend.` prefix) and real source paths relative to the package root;
 * `planned` entries carry neither. Each sub-project flips its own entries.
 *
 * @phpstan-type Pattern array{key: string, group: string, title: string, description: string, status: 'built'|'planned', route: string|null, sources: list<string>, subProject: int}
 */
final class PatternCatalog
{
    /** @var list<string> */
    public const GROUPS = ['lists', 'forms', 'pages', 'components', 'integrations'];

    /**
     * @return list<Pattern>
     */
    public static function patterns(): array
    {
        return [
            // Lists
            self::built('list-table', 'lists', 'Table list', 'Sortable columns, search box, per-page selector, filters in a drawer.', 'example.index', ['resources/views/components/example-list']),
            self::built('list-modal-crud', 'lists', 'Modal CRUD on the index page', 'Create and edit in a modal without leaving the list; status toggler per row.', 'example.category', ['resources/views/components/category-list', 'resources/views/components/category-form']),
            self::built('list-card-grid', 'lists', 'Card grid', 'Responsive card layout with status filter chips and counts.', 'example.lists.card-grid', ['resources/views/components/lists/card-grid'], 2),
            self::built('list-bulk-actions', 'lists', 'Bulk select + bulk actions', 'Row checkboxes, select page / all matching, selection bar with status change and confirmed delete.', 'example.lists.bulk', ['resources/views/components/lists/bulk-actions'], 2),
            self::built('list-trashed', 'lists', 'Trashed toggle + restore', 'Active/Trash views with restore and confirmed delete-forever.', 'example.lists.trashed', ['resources/views/components/lists/trashed'], 2),
            self::built('list-sortable', 'lists', 'Drag-sortable rows', 'Reorder a category\'s examples by dragging (wire:sort), saved to position.', 'example.lists.sortable', ['resources/views/components/lists/sortable'], 2),
            self::built('list-tree', 'lists', 'Tree list with drag reorder', 'Nested nodes: expand/collapse, drag across parents (wire:sort groups), inline add/rename, branch delete.', 'example.nodes', ['resources/views/components/node-tree', 'resources/views/partials/node-branch.blade.php'], 2),
            self::built('list-grouped', 'lists', 'Grouped table', 'Rows under collapsible status headers with counts.', 'example.lists.grouped', ['resources/views/components/lists/grouped'], 2),
            self::built('list-expandable', 'lists', 'Expandable rows', 'Click a row to reveal content, meta and tags inline.', 'example.lists.expandable', ['resources/views/components/lists/expandable'], 2),
            self::built('list-load-more', 'lists', 'Load more / infinite scroll', 'wire:intersect sentinel with a Load more button fallback.', 'example.lists.load-more', ['resources/views/components/lists/load-more'], 2),
            self::built('list-inline-edit', 'lists', 'Inline-edit list', 'Tags edited in the row (name + color picker) with an add row at the bottom.', 'example.tags', ['resources/views/components/tag-list'], 2),
            self::built('list-inline-filters', 'lists', 'Inline filter row', 'Search, status, category and due range above the table, kept in the URL.', 'example.lists.inline-filters', ['resources/views/components/lists/inline-filters', 'src/Livewire/Concerns/FiltersExamples.php', 'resources/views/partials/example-filter-row.blade.php'], 2),
            self::built('list-export', 'lists', 'Export to Excel', 'Sync download and queued export with progress, sharing the page filters.', 'example.lists.export', ['resources/views/components/lists/export', 'src/Exports/ExampleExportQuery.php'], 2),

            // Forms
            self::built('form-long-fixed', 'forms', 'Long form with fixed action buttons', 'Dedicated page, form object, fixed bottom-right save bar.', 'example.form', ['resources/views/components/example-form', 'src/Livewire/Forms/ExampleFormObject.php']),
            self::built('form-modal-inline', 'forms', 'Short form in a modal', 'Inline buttons inside a modal, form object, slug from name.', 'example.category', ['resources/views/components/category-form', 'src/Livewire/Forms/CategoryFormObject.php']),
            self::planned('form-sticky-bar', 'forms', 'Sticky action bar + error summary', 'Long editor with a sticky save bar and an error list at the top.', 3),
            self::planned('form-modal-child', 'forms', 'Modal hosting a child form component', 'The modal body is its own Livewire component.', 3),
            self::planned('form-drawer', 'forms', 'Create/edit in a drawer', 'Slide-over form next to the list.', 3),
            self::planned('form-tabbed', 'forms', 'Tabbed form', 'Fields split across tabs with per-tab error markers.', 3),
            self::planned('form-modal-wizard', 'forms', 'Modal wizard', 'Multi-step form in a modal with the steps indicator.', 3),
            self::planned('form-page-wizard', 'forms', 'Page wizard', 'Full-page multi-step form with back/next and a review step.', 3),
            self::planned('form-repeater', 'forms', 'Key/value repeater', 'Add, remove and reorder rows bound to a JSON column.', 3),
            self::planned('form-uploads', 'forms', 'Image + multi-file upload', 'Drag-n-drop image with preview and a multi-file drop zone.', 3),
            self::planned('form-rich-text', 'forms', 'Rich text', 'Formatted content field.', 3),
            self::planned('form-remote-select', 'forms', 'Remote + creatable select', 'adv-select searching the server and creating tags on the fly.', 3),
            self::planned('form-conditional', 'forms', 'Conditional fields', 'Show fields depending on other values (server and client side).', 3),
            self::planned('form-blur-validation', 'forms', 'Real-time validation', 'Validate on blur with wire:model.blur.', 3),
            self::planned('form-slug', 'forms', 'Slug auto-fill', 'Slug follows the title until edited by hand.', 3),
            self::planned('form-json', 'forms', 'JSON field', 'Monospace JSON editor with validation.', 3),
            self::planned('form-color', 'forms', 'Color picker', 'Swatches plus a custom hex value.', 3),

            // Pages
            self::built('page-catalog', 'pages', 'Pattern catalog', 'This page: registry-driven card grid with search, group filter and copy-path buttons.', 'example.catalog', ['resources/views/components/pattern-catalog', 'src/Catalog/PatternCatalog.php']),
            self::planned('page-detail-tabs', 'pages', 'Detail page with tabs', 'Show page whose tabs host nested Livewire components.', 4),
            self::planned('page-dashboard', 'pages', 'Dashboard', 'Stat tiles and lazy-loaded panels.', 4),
            self::planned('page-settings', 'pages', 'Tabbed settings page', 'Module settings stored with DbConfig.', 4),
            self::planned('page-report', 'pages', 'Report with filters + export', 'Inline filters, totals and a queued export.', 4),
            self::planned('page-print', 'pages', 'Print view', 'Print-styled page (browser print to PDF).', 4),
            self::planned('page-kanban', 'pages', 'Kanban board', 'Examples by status with drag between columns.', 4),
            self::planned('page-calendar', 'pages', 'Calendar', 'Month view of examples by due date.', 4),
            self::planned('page-split-pane', 'pages', 'Split pane', 'Tree on the left, editor on the right.', 4),
            self::planned('page-empty-states', 'pages', 'Empty states', 'First-run, no-results and error states.', 4),
            self::planned('page-progress', 'pages', 'Live progress page', 'Polling progress for a running import.', 4),

            // Components
            self::built('comp-form-fields', 'components', 'Form field components', 'adv-select (single/multi), currency, datepicker, password, radio buttons, checkboxes.', 'example.form', ['resources/views/components/example-form/example-form.blade.php']),
            self::built('comp-copy-button', 'components', 'Copy button', 'x-synapse-copy-button next to each source path on this page.', 'example.catalog', ['resources/views/components/pattern-catalog/pattern-catalog.blade.php']),
            self::planned('comp-gallery', 'components', 'Component gallery', 'Alerts, badges, panels, stat tiles, lightbox, toastr, confirm dialog in one place.', 4),

            // Integrations
            self::planned('int-auth-helpers', 'integrations', 'Auth directives + helpers', '@canAccess, @hasRole, @isDev and the auth helper functions.', 5),
            self::planned('int-activity-log', 'integrations', 'Activity log + viewer', 'log_activity() with before/after snapshots and the embedded viewer.', 5),
            self::planned('int-otp', 'integrations', 'OTP-protected action', 'Re-verify with a one-time password before a sensitive action.', 5),
            self::planned('int-abac', 'integrations', 'Conditional permission', 'ABAC condition evaluated against the record.', 5),
            self::planned('int-guard', 'integrations', 'Guarded actions', 'GuardsBackendPermission inside Livewire actions.', 5),
            self::planned('int-api', 'integrations', 'API v1', 'Token-authenticated JSON endpoints with API resources.', 5),
            self::planned('int-hmac', 'integrations', 'HMAC-signed endpoint', 'api.signed request signing.', 5),
            self::planned('int-notify', 'integrations', 'Notifications', 'Notify to users/roles with bell + toastr.', 5),
            self::planned('int-search', 'integrations', 'Global search', 'Examples in the Ctrl/Cmd+K palette.', 5),
            self::planned('int-import', 'integrations', 'Excel import', 'Queued import with validation and progress.', 5),
            self::planned('int-user-field', 'integrations', 'User field extension', 'Add a field to the user form/model via SynAuthExtension.', 5),
            self::planned('int-frontend', 'integrations', 'Frontend pages', 'Public list, detail and search pages on the theme layout.', 5),
            self::planned('int-remember', 'integrations', 'Remembered filters', 'RemembersQueryParams restores list filters on return.', 5),
        ];
    }

    /**
     * @param  list<string>  $sources
     * @return Pattern
     */
    private static function built(string $key, string $group, string $title, string $description, string $route, array $sources, int $subProject = 1): array
    {
        return [
            'key' => $key,
            'group' => $group,
            'title' => $title,
            'description' => $description,
            'status' => 'built',
            'route' => $route,
            'sources' => $sources,
            'subProject' => $subProject,
        ];
    }

    /**
     * @return Pattern
     */
    private static function planned(string $key, string $group, string $title, string $description, int $subProject): array
    {
        return [
            'key' => $key,
            'group' => $group,
            'title' => $title,
            'description' => $description,
            'status' => 'planned',
            'route' => null,
            'sources' => [],
            'subProject' => $subProject,
        ];
    }
}

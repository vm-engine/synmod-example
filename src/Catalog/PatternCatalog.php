<?php

declare(strict_types=1);

namespace VmEngine\Example\Catalog;

/**
 * Static index of every UI / integration pattern the example module demonstrates
 * (or will). `built` entries must point at a real backend route name (without
 * the `backend.` prefix) and real source paths relative to the package root;
 * `planned` entries carry neither. Each sub-project flips its own entries.
 *
 * `route` is a backend route name, or `frontend:<name>` for a public route.
 *
 * @phpstan-type Pattern array{key: string, group: string, title: string, description: string, status: 'built'|'planned', route: string|null, sources: list<string>, subProject: int}
 */
final class PatternCatalog
{
    /** @var list<string> */
    public const GROUPS = ['lists', 'forms', 'pages', 'components', 'integrations'];

    /**
     * Link for a built pattern: a backend route, or `frontend:<name>` for a public one.
     *
     * @param  Pattern  $pattern
     */
    public static function url(array $pattern): ?string
    {
        $route = $pattern['route'];

        if ($route === null) {
            return null;
        }

        return str_starts_with($route, 'frontend:') ? route(substr($route, 9)) : backend_route($route);
    }

    /**
     * Full route name to look up with Route::has().
     */
    public static function routeName(string $route): string
    {
        return str_starts_with($route, 'frontend:') ? substr($route, 9) : 'backend.'.$route;
    }

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
            self::built('form-sticky-bar', 'forms', 'Sticky action bar + error summary', 'Full create/edit editor; the fixed bar shows the error count and links each message to its field.', 'example.editor', ['resources/views/components/example-editor', 'resources/views/partials/error-summary.blade.php', 'src/Livewire/Forms/ExampleEditorForm.php'], 3),
            self::built('form-modal-child', 'forms', 'Modal hosting a child form component', 'The modal body is its own Livewire component; it emits example-saved and the list refreshes.', 'example.forms.modal-child', ['resources/views/components/forms/modal-child', 'resources/views/components/forms/quick-edit'], 3),
            self::built('form-drawer', 'forms', 'Create/edit in a drawer', 'Slide-over form next to the list, opened/closed by browser events.', 'example.forms.drawer', ['resources/views/components/forms/drawer-form'], 3),
            self::built('form-tabbed', 'forms', 'Tabbed form', 'General / Content / Meta tabs with per-tab error counts.', 'example.forms.tabbed', ['resources/views/components/forms/tabbed'], 3),
            self::built('form-modal-wizard', 'forms', 'Modal wizard', 'Three steps in a modal with x-synapse-steps and per-step validation.', 'example.forms.modal-wizard', ['resources/views/components/forms/modal-wizard', 'src/Livewire/Concerns/WizardSteps.php'], 3),
            self::built('form-page-wizard', 'forms', 'Page wizard', 'Four-step page: back never validates, review step, create with tags.', 'example.forms.page-wizard', ['resources/views/components/forms/page-wizard', 'src/Livewire/Concerns/WizardSteps.php'], 3),
            self::built('form-repeater', 'forms', 'Key/value repeater', 'Add, remove and drag-reorder rows bound to the meta JSON column.', 'example.editor', ['resources/views/partials/fields/repeater.blade.php', 'src/Livewire/Concerns/HasMetaRepeater.php'], 3),
            self::built('form-uploads', 'forms', 'Image + multi-file upload', 'Cover via drag-n-drop and attachments via file-drop, stored per example.', 'example.editor', ['resources/views/partials/fields/uploads.blade.php', 'src/Livewire/Concerns/HasAttachments.php'], 3),
            self::built('form-rich-text', 'forms', 'Rich text', 'Lazy-loaded Jodit bound to Livewire; HTML purified on save.', 'example.editor', ['src/View/Components/RichText.php', 'resources/views/blade/rich-text.blade.php', 'resources/js/example.js', 'src/Support/RichTextSanitizer.php'], 3),
            self::built('form-remote-select', 'forms', 'Remote + creatable select', 'adv-select searching tags on the server and creating new ones on Enter.', 'example.editor', ['resources/views/partials/fields/tags.blade.php', 'src/Livewire/Concerns/HasTagSearch.php', 'src/Support/TagResolver.php'], 3),
            self::built('form-conditional', 'forms', 'Conditional fields', 'Server-side publish note for Published; client-side due date behind Schedule.', 'example.editor', ['resources/views/partials/fields/conditional.blade.php'], 3),
            self::built('form-blur-validation', 'forms', 'Real-time validation', 'wire:model.blur with validateOnly on title, slug and email.', 'example.editor', ['resources/views/components/example-editor'], 3),
            self::built('form-slug', 'forms', 'Slug auto-fill', 'Slug follows the title until edited by hand; unique across trashed rows.', 'example.editor', ['resources/views/partials/fields/slug.blade.php'], 3),
            self::built('form-json', 'forms', 'JSON field', 'Raw JSON toggle over the same meta data, validated as an object.', 'example.editor', ['resources/views/partials/fields/repeater.blade.php', 'src/Livewire/Forms/ExampleEditorForm.php'], 3),
            self::built('form-color', 'forms', 'Color picker', 'x-synapse-color-picker with swatches and hex_color validation.', 'example.editor', ['resources/views/components/example-editor'], 3),

            // Pages
            self::built('page-catalog', 'pages', 'Pattern catalog', 'This page: registry-driven card grid with search, group filter and copy-path buttons.', 'example.catalog', ['resources/views/components/pattern-catalog', 'src/Catalog/PatternCatalog.php']),
            self::built('page-detail-tabs', 'pages', 'Detail page with tabs', 'Overview / Attachments / Related; the tab lives in the URL and the last two are lazy child components (lightbox, confirm delete).', 'example.show', ['resources/views/components/pages/detail', 'resources/views/components/pages/detail-attachments', 'resources/views/components/pages/detail-related'], 4),
            self::built('page-dashboard', 'pages', 'Dashboard', 'Stat tiles, ApexCharts donut + line, lazy panels with skeletons.', 'example.dashboard', ['resources/views/components/pages/dashboard', 'resources/views/components/pages/dashboard-panel', 'src/Support/ExampleStats.php', 'src/View/Components/Chart.php'], 4),
            self::built('page-settings', 'pages', 'Tabbed settings page', 'Lists / Editor / Board settings in DbConfig, each tab saved on its own and read by the other pages.', 'example.settings', ['resources/views/components/pages/settings', 'src/Support/ExampleSettings.php'], 4),
            self::built('page-report', 'pages', 'Report with filters + export', 'Status × category matrix with totals, bar chart updated in place, print view and queued export.', 'example.report', ['resources/views/components/pages/report', 'resources/views/partials/status-matrix.blade.php'], 4),
            self::built('page-print', 'pages', 'Print view', 'Bare print layout with the report filters (browser print to PDF).', 'example.report.print', ['resources/views/components/pages/report-print', 'resources/views/layouts/print.blade.php'], 4),
            self::built('page-kanban', 'pages', 'Kanban board', 'Status columns with drag between them, publish-note gate and WIP limit badge.', 'example.kanban', ['resources/views/components/pages/kanban'], 4),
            self::built('page-calendar', 'pages', 'Calendar', 'Month view of due dates; click a day to quick-create.', 'example.calendar', ['resources/views/components/pages/calendar'], 4),
            self::built('page-split-pane', 'pages', 'Split pane', 'Node tree on the left, editor with path and children on the right.', 'example.nodes.browse', ['resources/views/components/pages/node-browser', 'resources/views/partials/node-browse-branch.blade.php'], 4),
            self::built('page-empty-states', 'pages', 'Empty states', 'First-run, no-results and error states from one partial (also used by the list).', 'example.empty-states', ['resources/views/partials/empty-state.blade.php', 'resources/views/components/pages/empty-states'], 4),
            self::built('page-progress', 'pages', 'Live progress page', 'wire:poll progress for a running export; stops when done and offers the download.', 'example.progress', ['resources/views/components/pages/progress'], 4),

            // Components
            self::built('comp-form-fields', 'components', 'Form field components', 'adv-select (single/multi), currency, datepicker, password, radio buttons, checkboxes.', 'example.form', ['resources/views/components/example-form/example-form.blade.php']),
            self::built('comp-copy-button', 'components', 'Copy button', 'x-synapse-copy-button next to each source path on this page.', 'example.catalog', ['resources/views/components/pattern-catalog/pattern-catalog.blade.php']),
            self::built('comp-gallery', 'components', 'Component gallery', 'Alerts, badges, panels, stat tiles, lightbox, toastr, confirm dialog and drawer in one place.', 'example.components', ['resources/views/components/pages/component-gallery'], 4),

            // Integrations
            self::built('int-auth-helpers', 'integrations', 'Auth directives + helpers', '@canAccess, @hasRole, @isDev and friends next to their live result for you, plus the auth helper functions.', 'example.integrations.auth-helpers', ['resources/views/components/integrations/auth-helpers'], 5),
            self::built('int-activity-log', 'integrations', 'Activity log + viewer', 'An observer logs every example write with whitelisted before/after snapshots; the page embeds the OTP-gated metadata viewer.', 'example.integrations.activity', ['resources/views/components/integrations/activity', 'src/Observers/ExampleObserver.php', 'src/Support/ExampleActivity.php'], 5),
            self::built('int-otp', 'integrations', 'OTP-protected action', 'Empty trash asks for a one-time password; a single-use, purpose-bound token unlocks the delete.', 'example.lists.trashed', ['resources/views/components/lists/trashed'], 5),
            self::built('int-abac', 'integrations', 'Conditional permission', 'Example author role: update/delete only your own examples (created_by = $user.id), checked with the record.', 'example.integrations.abac', ['resources/views/components/integrations/abac', 'src/Support/ExampleAuthorRole.php'], 5),
            self::built('int-guard', 'integrations', 'Guarded actions', 'Every Livewire action in the module starts with guardAction() from GuardsBackendPermission.', 'example.integrations.auth-helpers', ['resources/views/components/integrations/auth-helpers', 'resources/views/components/lists/trashed'], 5),
            self::built('int-api', 'integrations', 'API v1', 'Token-authenticated JSON endpoints with API resources and FormRequests; Postman collection included.', 'example.integrations.api', ['routes/api.v1.php', 'src/Http/Controllers/Api/ExampleApiController.php', 'src/Http/Resources/ExampleResource.php', 'docs/postman/example-api-v1.postman_collection.json'], 5),
            self::built('int-hmac', 'integrations', 'HMAC-signed endpoint', 'POST /examples needs X-Timestamp + X-Signature (api.signed); the Postman request signs itself.', 'example.integrations.api', ['routes/api.v1.php', 'src/Http/Requests/StoreExampleRequest.php'], 5),
            self::built('int-notify', 'integrations', 'Notifications', 'Publishing notifies admins (bell + toast), imports notify the importer; lang keys rendered per viewer.', 'example.integrations.notifications', ['resources/views/components/integrations/notifications', 'src/Notifications', 'src/Support/ExampleNotifier.php'], 5),
            self::built('int-search', 'integrations', 'Global search', 'Examples and key pages in the Ctrl/Cmd+K palette, kept in sync by the observer.', 'example.catalog', ['src/Support/ExampleSearchIndex.php', 'src/Console/SearchReindexCommand.php', 'module.json'], 5),
            self::built('int-import', 'integrations', 'Excel import', 'Queued import with per-row validation, a skipped-rows report, live progress and a template.', 'example.integrations.import', ['resources/views/components/integrations/import', 'src/Imports/ExampleImportProcessor.php'], 5),
            self::built('int-user-field', 'integrations', 'User field extension', 'A default example category on the backend user form (SynAuthExtension), preselected in the editor and calendar.', 'example.editor', ['database/migrations/2026_10_07_000001_add_example_default_category_to_users_table.php', 'src/ExampleServiceProvider.php', 'resources/views/partials/user-default-category.blade.php', 'src/Support/UserDefaultCategory.php'], 5),
            self::built('int-frontend', 'integrations', 'Frontend pages', 'Public list, detail and search of published examples on the theme layout.', 'frontend:example.index', ['routes/web.php', 'resources/views/components/frontend', 'src/Livewire/Concerns/FrontendPage.php'], 5),
            self::built('int-remember', 'integrations', 'Remembered filters', 'RemembersQueryParams restores the example list and inline filters on return; the URL always wins.', 'example.index', ['resources/views/components/example-list', 'resources/views/components/lists/inline-filters'], 5),
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
}

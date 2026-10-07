<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::livewire('/', 'example::example-list')->name('index')
        ->middleware('can-access:example.manage');
    Route::livewire('/catalog', 'example::pattern-catalog')->name('catalog')
        ->middleware('can-access:example.any');
    Route::livewire('/form/{id?}', 'example::example-form')->name('form')
        ->middleware('can-access:example.manage.create|example.manage.update');
    Route::livewire('/category', 'example::category-list')->name('category')
        ->middleware('can-access:example.category');
    Route::livewire('/tags', 'example::tag-list')->name('tags')
        ->middleware('can-access:example.tag');
    Route::livewire('/nodes', 'example::node-tree')->name('nodes')
        ->middleware('can-access:example.node');

    Route::livewire('/editor/{id?}', 'example::example-editor')->name('editor')
        ->middleware('can-access:example.manage.create|example.manage.update');

    Route::prefix('forms')->name('forms.')->group(function () {
        Route::livewire('/modal-child', 'example::forms.modal-child')->name('modal-child')
            ->middleware('can-access:example.manage.update');
        Route::livewire('/drawer', 'example::forms.drawer-form')->name('drawer')
            ->middleware('can-access:example.manage.create|example.manage.update');
        Route::livewire('/tabbed/{id?}', 'example::forms.tabbed')->name('tabbed')
            ->middleware('can-access:example.manage.create|example.manage.update');
        Route::livewire('/modal-wizard', 'example::forms.modal-wizard')->name('modal-wizard')
            ->middleware('can-access:example.manage.create');
        Route::livewire('/page-wizard', 'example::forms.page-wizard')->name('page-wizard')
            ->middleware('can-access:example.manage.create');
    });

    Route::livewire('/dashboard', 'example::pages.dashboard')->name('dashboard')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/report', 'example::pages.report')->name('report')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/report/print', 'example::pages.report-print')->name('report.print')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/progress/{task?}', 'example::pages.progress')->name('progress')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/examples/{id?}', 'example::pages.detail')->name('show')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/settings', 'example::pages.settings')->name('settings')
        ->middleware('can-access:example.settings');
    Route::livewire('/kanban', 'example::pages.kanban')->name('kanban')
        ->middleware('can-access:example.manage.update');
    Route::livewire('/calendar', 'example::pages.calendar')->name('calendar')
        ->middleware('can-access:example.manage.read');
    Route::livewire('/nodes/browse', 'example::pages.node-browser')->name('nodes.browse')
        ->middleware('can-access:example.node');
    Route::livewire('/empty-states', 'example::pages.empty-states')->name('empty-states')
        ->middleware('can-access:example.manage');
    Route::livewire('/components', 'example::pages.component-gallery')->name('components')
        ->middleware('can-access:example.any');
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::livewire('/auth-helpers', 'example::integrations.auth-helpers')->name('auth-helpers')
            ->middleware('can-access:example.any');
        Route::livewire('/activity', 'example::integrations.activity')->name('activity')
            ->middleware('can-access:example.manage.read');
        Route::livewire('/abac', 'example::integrations.abac')->name('abac')
            ->middleware('can-access:example.manage.read');
        Route::livewire('/api', 'example::integrations.api')->name('api')
            ->middleware('can-access:example.manage.read');
        Route::livewire('/notifications', 'example::integrations.notifications')->name('notifications')
            ->middleware('can-access:example.manage.read');
        Route::livewire('/import/{task?}', 'example::integrations.import')->name('import')
            ->middleware('can-access:example.manage.create');
    });

    Route::prefix('lists')->name('lists.')->group(function () {
        Route::livewire('/card-grid', 'example::lists.card-grid')->name('card-grid')
            ->middleware('can-access:example.manage');
        Route::livewire('/bulk', 'example::lists.bulk-actions')->name('bulk')
            ->middleware('can-access:example.manage');
        Route::livewire('/trashed', 'example::lists.trashed')->name('trashed')
            ->middleware('can-access:example.manage.delete');
        Route::livewire('/sortable', 'example::lists.sortable')->name('sortable')
            ->middleware('can-access:example.manage.update');
        Route::livewire('/grouped', 'example::lists.grouped')->name('grouped')
            ->middleware('can-access:example.manage');
        Route::livewire('/expandable', 'example::lists.expandable')->name('expandable')
            ->middleware('can-access:example.manage');
        Route::livewire('/load-more', 'example::lists.load-more')->name('load-more')
            ->middleware('can-access:example.manage');
        Route::livewire('/inline-filters', 'example::lists.inline-filters')->name('inline-filters')
            ->middleware('can-access:example.manage');
        Route::livewire('/export', 'example::lists.export')->name('export')
            ->middleware('can-access:example.manage');
    });
});

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

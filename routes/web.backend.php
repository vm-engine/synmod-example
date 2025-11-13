<?php

use Illuminate\Support\Facades\Route;
use VmEngine\Example\Livewire\CategoryList;
use VmEngine\Example\Livewire\ExampleForm;
use VmEngine\Example\Livewire\ExampleList;

Route::group([], function () {
    Route::get('/', ExampleList::class)->name('index')
        ->middleware('can-access:example.manage');
    Route::get('/form/{id?}', ExampleForm::class)->name('form')
        ->middleware('can-access:example.manage.create|example.manage.update');
    Route::get('/category', CategoryList::class)->name('category')
        ->middleware('can-access:example.category');
});

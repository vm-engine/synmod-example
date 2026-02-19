<?php

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::livewire('/', 'example.example-list')->name('index')
        ->middleware('can-access:example.manage');
    Route::livewire('/form/{id?}', 'example.example-form')->name('form')
        ->middleware('can-access:example.manage.create|example.manage.update');
    Route::livewire('/category', 'example.category-list')->name('category')
        ->middleware('can-access:example.category');
});

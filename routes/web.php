<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * Public pages (prefix /example, names example.*). Published examples only.
 * /search is registered before /{slug} so it is not read as a slug.
 */
Route::livewire('/', 'example::frontend.index')->name('index');
Route::livewire('/search', 'example::frontend.search')->name('search');
Route::livewire('/{slug}', 'example::frontend.show')->name('show')->where('slug', '[a-z0-9-]+');

<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::index')->name('pages.index');
Route::livewire('/lab', 'pages::lab')->name('pages.lab');
Route::livewire('/review', 'pages::review')->name('pages.review');

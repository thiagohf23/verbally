<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::today')->name('pages.today');
Route::livewire('/writing', 'pages::index')->name('pages.index');
Route::livewire('/lab', 'pages::lab')->name('pages.lab');
Route::livewire('/review', 'pages::review')->name('pages.review');
Route::livewire('/conversations', 'pages::conversations')->name('pages.conversations');
Route::livewire('/listen', 'pages::listen')->name('pages.listen');
Route::livewire('/today', 'pages::today')->name('pages.today_alias');

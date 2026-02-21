<?php

use Illuminate\Support\Facades\Route;

Route::livewire('deportes', 'pages::admin.deportes.index')->name('deportes.index');

Route::livewire('temporadas', 'pages::admin.temporadas.index')->name('temporadas.index');
Route::livewire('temporadas/create', 'pages::admin.temporadas.create')->name('temporadas.create');
Route::livewire('temporadas/{temporada}', 'pages::admin.temporadas.show')->name('temporadas.show');
Route::livewire('temporadas/{temporada?}/edit', 'pages::admin.temporadas.create')->name('temporadas.edit');

Route::livewire('eventos', 'pages::admin.eventos.index')->name('eventos.index');
Route::livewire('eventos/{evento:slug}', 'pages::admin.eventos.show')->name('eventos.show');

Route::livewire('users', 'pages::admin.users.index')->name('users.index');
Route::livewire('users/{user}', 'pages::admin.users.show')->name('users.show');

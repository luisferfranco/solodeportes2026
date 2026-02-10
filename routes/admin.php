<?php

Route::livewire('deportes', 'pages::admin.deportes.index')->name('deportes.index');

Route::livewire('temporadas', 'pages::admin.temporadas.index')->name('temporadas.index');
Route::livewire('temporadas/{temporada}', 'pages::admin.temporadas.show')->name('temporadas.show');
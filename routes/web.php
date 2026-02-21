<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::users.index');

Route::livewire('/register', 'pages::auth.register')->name('register');
Route::livewire('/login', 'pages::auth.login')->name('login');
Route::get('/logout', function () {
    auth()->logout();

    return redirect(route('login'));
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('/banco/{user?}', 'pages::banco.index')->name('banco');
    Route::livewire('/transaccion/{transaccion}', 'pages::banco.show')->name('banco.show');
    Route::livewire('/deposito', 'pages::banco.deposito')->name('banco.deposito');
    Route::livewire('/retiro', 'pages::banco.retiro')->name('banco.retiro');

    Route::livewire('/notificaciones', 'pages::notificaciones')->name('notificaciones');

    Route::livewire('/tienda', 'pages::tienda.index')->name('tienda');

    // Rutas para eventos
    // {deporte}/qn/{evento}/... Quiniela
    // {deporte}/sr/{evento}/... Survivor
    // {deporte}/jk/{evento}/... Jackpot

    // Comunes
    Route::livewire('/evento/{evento:slug}', 'pages::evento.show')->name('evento.show');
    Route::livewire('/evento/{evento:slug}/resultados/{participacion?}', 'pages::evento.resultados')->name('evento.resultados');
    Route::livewire('/evento/{evento:slug}/marcadores', 'pages::evento.marcadores')->name('evento.marcadores');
    Route::livewire('/evento/{evento:slug}/leaderboard', 'pages::evento.leaderboard')->name('evento.leaderboard');

    // Futbol Soccer
    Route::livewire('/fb/qn/{evento:slug}/pronosticos', 'pages::fb.qn.pronosticos')->name('fb.qn.pronosticos');

    // Futbol Americano - Quiniela
    Route::livewire('/fa/qn/{evento:slug}/pronosticos', 'pages::fa.qn.pronosticos')->name('fa.qn.pronosticos');

    // Futbol Americano - Survivor
    Route::livewire('/fa/sr/{evento:slug}', 'pages::fb.qn.show')->name('fa.sr.show');

});

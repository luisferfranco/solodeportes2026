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

    // Futbol Soccer
    Route::livewire('/fb/qn/resultados/{participacion?}', 'pages::fb.qn.resultados')->name('fb.qn.resultados');
    Route::livewire('/fb/qn/{evento}', 'pages::fb.qn.show')->name('fb.qn.show');
    Route::livewire('/fb/qn/{evento}/leaderboard', 'pages::fb.qn.pronosticos')->name('fb.qn.leaderboard');
    Route::livewire('/fb/qn/{evento}/pronosticos', 'pages::fb.qn.pronosticos')->name('fb.qn.pronosticos');
    Route::livewire('/fb/qn/{evento}/marcadores', 'pages::fb.qn.pronosticos')->name('fb.qn.marcadores');

    // Futbol Americano
    Route::livewire('/fa/qn/{evento}', 'pages::fb.qn.show')->name('fa.qn.show');
    Route::livewire('/fa/sr/{evento}', 'pages::fb.qn.show')->name('fa.sr.show');
});

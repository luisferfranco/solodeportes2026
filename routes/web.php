<?php

Route::livewire('/', 'pages::users.index');

Route::livewire('/register', 'pages::auth.register')->name('register');
Route::livewire('/login', 'pages::auth.login')->name('login');
Route::get('/logout', function() {
  auth()->logout();
  return redirect(route('login'));
})->name('logout');

Route::middleware('auth')->group(function () {
  Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');

  Route::livewire('/banco/{user?}', 'pages::banco.index')->name('banco');
  Route::livewire('/transaccion/{transaccion}', 'pages::banco.show')->name('banco.show');
  Route::livewire('/deposito', 'pages::banco.deposito')->name('banco.deposito');
  Route::livewire('/retiro', 'pages::banco.retiro')->name('banco.retiro');
});

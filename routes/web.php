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
});

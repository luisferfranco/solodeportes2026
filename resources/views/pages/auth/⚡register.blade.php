<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts.empty')]
class extends Component
{
  public $name, $email, $password, $password_confirmation;

  public function register() {
    $this->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
      'name' => $this->name,
      'email' => $this->email,
      'password' => bcrypt($this->password),
    ]);

    auth()->login($user, true);

    return $this->redirectIntended(route('dashboard'));
  }

};
?>

<div class="relative bg-base-100 p-4 rounded-xl shadow-lg max-w-4xl w-full">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-12 mb-6 font-bold">Registro</h1>

  <x-form wire:submit='register' class="w-full space-y-2">
    <x-input wire:model='name' label="Nombre" placeholder="Nombre" inline required class="outline-none!" autofocus />
    <x-input wire:model='email' label="Correo electrónico" placeholder="Correo electrónico" inline required class="outline-none!" />
    <x-input wire:model='password' label="Contraseña" placeholder="Contraseña" type="password" inline required class="outline-none!" />
    <x-input wire:model='password_confirmation' label="Confirmar contraseña" placeholder="Confirmar contraseña" type="password" inline required class="outline-none!" />
    <div class="flex justify-between items-center">
      <a href="{{ route('login') }}" class="text-sm text-base-content/50 hover:underline hover:text-base-content">¿Ya tienes una cuenta? Inicia sesión</a>
      <x-button type="submit" class="ml-auto btn-primary">Registrarse</x-button>
    </div>
  </x-form>

</div>
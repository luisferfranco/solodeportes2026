<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts.empty')]
class extends Component
{
    public $email, $password;

    public function login() {
        $this->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (auth()->attempt(['email' => $this->email, 'password' => $this->password], true)) {
            return $this->redirectIntended(route('dashboard'));
        }

        $this->addError('email', 'Las credenciales no son correctas.');
    }
};
?>

<div class="relative bg-base-100 p-4 rounded-xl shadow-lg max-w-4xl w-full">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-12 mb-6 font-bold">Inicio de sesión</h1>

  <x-form wire:submit='login' class="w-full space-y-2">
    <x-input wire:model='email' label="Correo electrónico" placeholder="Correo electrónico" inline required class="outline-none!" autofocus />
    <x-input wire:model='password' label="Contraseña" placeholder="Contraseña" type="password" inline required class="outline-none!" />
    <div class="flex justify-between items-center">
      <a href="{{ route('register') }}" class="text-sm text-base-content/50 hover:underline hover:text-base-content">¿No tienes una cuenta? Regístrate</a>
      <x-button type="submit" class="ml-auto btn-primary">Iniciar sesión</x-button>
    </div>
  </x-form>

</div>
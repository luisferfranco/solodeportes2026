<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts.empty')]
class extends Component
{
  public $name, $email, $password, $password_confirmation;

  public function mount() {
    if (auth()->check()) {
      return $this->redirect(route('dashboard'));
    }
  }

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

<x-card class="relative bg-base-100 max-w-xl w-full border border-base-100">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-12 mb-6 font-bold">Registro</h1>

  <x-form wire:submit='register' class="w-full space-y-2">
    <div>
      <x-label
        value="Nombre"
        required
        />
      <x-input
        wire:model='name'
        placeholder="Nombre"
        required
        class="outline-none!"
        icon="fas.user"
        autofocus
        />
    </div>

    <div>
      <x-label value="Correo Electrónico" required />
      <x-input
        wire:model='email'
        placeholder="Correo electrónico"
        required
        class="outline-none!"
        icon="fas.envelope"
        />
    </div>

    <div>
      <x-label value="Contraseña" required />
      <x-input
        wire:model='password'
        placeholder="Contraseña"
        type="password"
        required
        class="outline-none!"
        icon="fas.lock"
        />
    </div>

    <div>
      <x-label value="Confirmar Contraseña" required />
      <x-input
        wire:model='password_confirmation'
        placeholder="Confirmar Contraseña"
        type="password"
        required
        class="outline-none!"
        icon="fas.lock"
        />
    </div>

    <x-button type="submit" class="w-full btn-primary">Registrarse</x-button>


    <div class="text-center">
      ¿Ya tienes una cuenta?
      <a
        wire:navigate
        href="{{ route('login') }}"
        class="text-base-content/50 hover:text-primary hover:underline transition duration-500">Inicia sesión</a>
    </div>
  </x-form>

</x-card>
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts.empty')]
class extends Component
{
    public $email, $password;

    public function mount() {
      if (auth()->check()) {
        return $this->redirect(route('dashboard'));
      }
    }

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

<x-card class="relative bg-base-100 max-w-xl w-full border border-base-100">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-16 mb-6 font-bold">Inicio de sesión</h1>

  <x-form wire:submit='login' class="w-full space-y-2">
    <div>
      <x-label
        value="Correo electrónico"
        required
        />
      <x-input
        wire:model='email'
        placeholder="Correo electrónico"
        required
        class="outline-none!"
        icon="fas.envelope"
        autofocus
        />
    </div>

    <div>
      <div class="flex items-center justify-between">
        <x-label
          value="Contraseña"
          required
          />
        <a wire:navigate href="{{ route('solicitud-recuperacion') }}" class="text-xs text-base-content/70 hover:text-primary hover:underline transition duration-300">¿Olvidaste tu contraseña?</a>
      </div>
      <x-input
        wire:model='password'
        placeholder="Contraseña"
        type="password"
        required
        icon="fas.lock"
        class="outline-none!"
        />
    </div>

    <x-button type="submit" class="w-full btn-primary">INICIAR SESIÓN</x-button>

    <div class="text-center text-sm text-base-content/50">O continua con</div>

    <x-button
      no-wire-navigate
      link="/login-google"
      class="w-full btn-outline btn-secondary"
      icon="fab.google"
      label="Google"
      />


    <div class="text-center mt-4 text-base-content/50">
      ¿No tienes una cuenta?
      <a href="{{ route('register') }}" class="ml-2 text-base-content/70 hover:text-primary hover:underline transition duration-300">Regístrate</a>
    </div>
  </x-form>

</x-card>
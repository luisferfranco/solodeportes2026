<?php

use Livewire\Component;
use App\Models\User;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;

new
#[Layout('layouts.empty')]
class extends Component
{
  use Toast;

  public $invalido = false;
  public $expirado = false;
  public $user;
  public $password, $password_confirmation;

  public function mount($token) {
    $this->user = User::where('password_reset_token', $token)->first();

    if (!$this->user) {
      $this->invalido = true;
    } else if (now()->diffInMinutes($this->user->password_reset_expires_at) > 60) {
      $this->expirado = true;
    }
  }

  public function reinicio() {
    $this->validate([
      'password' => 'required|string|confirmed|min:8',
    ]);

    $this->user->password = bcrypt($this->password);
    $this->user->password_reset_token = null;
    $this->user->password_reset_expires_at = null;
    $this->user->save();

    auth()->login($this->user, true);

    $this->success(
      title : 'Contraseña reiniciada',
      description : 'Tu contraseña ha sido reiniciada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.',
      icon : 'o-check',
      redirectTo: route('dashboard')
    );
  }
};
?>

<x-card class="relative bg-base-100 max-w-xl w-full border border-base-100">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-16 mb-6 font-bold">Reinicio de Password</h1>

  @if ($invalido)
    <x-alert
      class="alert-error"
      title="Enlace Inválido"
      description="El enlace de reinicio de contraseña ha expirado."
      icon="o-exclamation-triangle"
      />
    <x-button
      link="{{ route('solicitud-recuperacion') }}"
      class="w-full btn-primary mt-6"
      label="Solicitar nuevo enlace"
      />
  @elseif ($expirado)
    <x-alert
      class="alert-error"
      title="Enlace expirado"
      icon="fas.clock"
      />
    <x-button
      link="{{ route('solicitud-recuperacion') }}"
      class="w-full btn-primary mt-6"
      label="Solicitar nuevo enlace"
      />
  @else
    <x-form wire:submit='reinicio' class="space-y-2">
      <div>
        <x-label
          value="Nueva contraseña"
          required
          />
        <x-input
          wire:model='password'
          placeholder="Nueva contraseña"
          type="password"
          required
          class="outline-none!"
          icon="fas.lock"
          autofocus
          />
      </div>

      <div>
        <x-label
          value="Confirmar nueva contraseña"
          required
          />
        <x-input
          wire:model='password_confirmation'
          placeholder="Confirmar nueva contraseña"
          type="password"
          required
          class="outline-none!"
          icon="fas.lock"
          />
      </div>

      <x-button type="submit" class="w-full">Reiniciar contraseña</x-button>
    </x-form>
  @endif
</x-card>
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\NotificacionRecuperacion;

new
#[Layout('layouts.empty')]
class extends Component
{
  use Toast;

  public $email;

  public function mount() {
    if (auth()->check()) {
      return $this->redirect(route('dashboard'));
    }
  }

  public function reinicio() {
      $this->validate([
          'email' => 'required|string|email',
      ]);

      $user = User::where('email', $this->email)->first();

      if ($user) {

        if ($user->external_id) {
          $this->addError('email', 'Este correo está asociado a una cuenta de Google. Inicia sesión con Google para acceder a tu cuenta.');
          return;
        }

        $user->password_reset_token = Str::random(60);
        $user->password_reset_expires_at = now()->addMinutes(60);
        $user->save();

        // Enviar correo electrónico
        $user->notify(new NotificacionRecuperacion($user));
      }

      $this->success(
        title: 'Solicitud enviada',
        description: 'Si el correo existe en nuestro sistema, recibirás un email con las instrucciones para restablecer tu contraseña.',
        timeout: 5000,
        icon: 'fas.check',
      );

  }
};
?>

<x-card class="relative bg-base-100 max-w-xl w-full border border-base-100">
  <img src="/img/solodeportes.png" class="absolute -top-24 left-1/2 transform -translate-x-1/2 w-48 h-48" alt="Logo">

  <h1 class="text-2xl text-base-content mt-16 mb-6 font-bold">Solicitud de reinicio de Password</h1>
  <x-form wire:submit='reinicio' class="w-full space-y-2">
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
        spinner
        />
    </div>

    <x-button type="submit" class="w-full btn-primary">Enviar Solicitud</x-button>
  </x-form>
</x-card>
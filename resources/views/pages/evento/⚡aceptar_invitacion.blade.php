<?php

use Livewire\Component;
use App\Models\Invitacion;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;
use App\Models\Participacion;

new
#[Layout('layouts.empty')]
class extends Component
{
  use Toast;

  public $invitacion;
  public $name, $email, $password, $password_confirmation;
  public $invalida = false;

  public function mount($code) {
    $this->invitacion = Invitacion::where('codigo', $code)->first();

    if (!$this->invitacion) {
      $this->invalida = true;
    } else {
      $this->name   = $this->invitacion->invitado->name;
      $this->email  = $this->invitacion->invitado->email;
    }

    if ($this->invitacion && $this->invitacion->accepted_at) {
      $this->success(
        title: '¡Invitación ya aceptada!',
        description: 'Ya has aceptado esta invitación el ' . $this->invitacion->accepted_at->format('d/m/Y H:i') . '. Te redireccionamos al evento...',
        timeout: 3000,
        icon: 'fas.hands-clapping',
        redirectTo: route('evento.show', ['evento' => $this->invitacion->evento])
      );
    }

  }

  public function aceptar() {
    $this->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255',
      'password' => 'nullable|string|min:8|confirmed',
    ]);

    $user = $this->invitacion->invitado;
    $user->name = $this->name;
    if ($this->password) {
      $user->password = bcrypt($this->password);
    }
    $user->save();

    $this->invitacion->accepted_at = now();
    $this->invitacion->save();

    // En los eventos por invitación, solo puede existir una participación
    // por usuario
    Participacion::updateOrCreate([
        'evento_id' => $this->invitacion->evento_id,
        'user_id'   => $user->id,
      ],
      [
        'nombre'    => $user->name,
      ]);

    Auth::login($user, true);

    $this->success(
      title: '¡Invitación aceptada!',
      description: 'Muchas gracias por aceptar la invitación, Ahora tienes acceso al evento',
      timeout: 3000,
      icon: 'fas.hands-clapping',
      redirectTo: route('evento.show', ['evento' => $this->invitacion->evento])
    );
  }

  public function rechazarInvitacion() {
    $this->invitacion->rejected_at = now();
    $this->invitacion->save();

    $this->error(
      title: '¡Invitación rechazada!',
      description: 'Has rechazado la invitación, ya no tendrás acceso al evento',
      icon: "fas.face-sad-tear",
      timeout: 3000,
      redirectTo: route('home')
    );
  }
};
?>

<div class="relative">
  <img
    src="/img/solodeplogo.png"
    class="absolute -top-16 left-1/2 -translate-x-1/2 w-32 h-auto"
    >

  <div
    class="bg-base-100 max-w-4xl px-6 pb-3 rounded-xl mx-auto shadow-xl border-base-300 border space-y-4 pt-16"
    >
    @if ($invalida)
      <x-alert
        class="alert-error"
        icon="fas.triangle-exclamation"
        title="¡Invitación inválida!"
        description="Lo sentimos, esta invitación no es válida. Por favor, verifica que el enlace sea correcto o solicita una nueva invitación al organizador del evento."
        />
    @elseif ($invitacion->rejected_at)
      <x-alert
        class="alert-error"
        icon="fas.triangle-exclamation"
        title="¡Invitación rechazada!"
        description="Has rechazado esta invitación el {{ $invitacion->rejected_at->format('d/m/Y H:i') }}. Si cambiaste de opinión, por favor solicita una nueva invitación al organizador del evento."
        />
    @elseif ($invitacion->caduca->isPast())
      <x-alert
        class="alert-error"
        icon="fas.triangle-exclamation"
        title="¡Invitación expirada!"
        description="Lo sentimos, esta invitación ha expirado el {{ $invitacion->caduca->format('d/m/Y H:i') }}. Por favor, solicita una nueva invitación al organizador del evento."
        />
    @else
      <p class="text-center text-3xl font-bold font-primary">¡Bienvenido a SoloDeportes.mx!</p>
      <p class="text-2xl font-bold font-primary text-center">¡Has sido invitado a un evento!</p>
      <p>Has recibido una invitación para participar en el evento <span class="font-bold font-primary">{{ $invitacion->evento->nombre }}</span>.</p>
      <p>Si deseas participar, por favor verifica los siguientes datos, si ya tenías cuenta en solodeportes.mx no es necesario que cambies tu contraseña, pero si es la primera vez que nos visitas, hazlo para poder acceder a tu cuenta posteriormente.</p>
      <form wire:submit='aceptar' class="space-y-4">
        <x-input
          wire:model='name'
          class="outline-none!"
          inline
          label="Nombre Completo"
          required
          />
        <x-input
          wire:model='email'
          class="outline-none!"
          inline
          label="Correo electrónico"
          disabled
          />

        <div class="px-4 py-2 bg-base-300 rounded-xl shadow-md space-y-4">
          <h1 class="font-bold text-xl">Cambio de Password</h1>
          <x-alert
            class="alert-warning"
            icon="fas.exclamation-triangle"
            title="¡Atención!"
            description="Si es la primera vez que visitas solodeportes.mx, por favor establece una contraseña para tu cuenta, de otra forma no podrás reingresar al sistema"
            />
          <x-input
            wire:model='password'
            class="outline-none!"
            inline
            label="Contraseña"
            type="password"
            placeholder="Constraeña"
            />
          <x-input
            wire:model='password_confirmation'
            class="outline-none!"
            inline
            label="Confirmar contraseña"
            type="password"
            placeholder="Confirmar contraseña"
            />
        </div>

        <x-alert
          class="alert-warning mt-4"
          icon="fas.exclamation-triangle"
          title="¡Atención!"
          description="Si rechazas la invitación, perderás el acceso al evento y no podrás participar en él. Requerirás solicitar una invitación nueva"
          />

        <div class="flex gap-1 w-full">
          <x-button
            class="btn-primary w-full flex-1"
            type="submit"
            label="Aceptar invitación"
            />
          <x-button
            class="btn-error w-full flex-1"
            type="button"
            label="Rechazar la Invitación"
            wire:click='rechazarInvitacion'
            />
        </div>
      </form>
    @endif
  </div>


</div>
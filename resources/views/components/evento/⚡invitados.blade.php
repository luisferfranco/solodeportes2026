<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\User;
use App\Models\Invitacion;
use App\Notifications\NotificacionInvitacionAEvento;
use Mary\Traits\Toast;
use App\Models\Participacion;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $invitaciones;
  public $name, $email;

  public $invitacion;
  public $modalCancelacion = false;
  public $modalEliminacion = false;

  public $headers;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->headers = [
      ['key' => 'invitado.name', 'label' => 'Nombre del invitado'],
      ['key' => 'created_at', 'label' => 'Envío'],
      ['key' => 'accepted_at', 'label' => 'Aceptación'],
    ];
  }

  public function invitar() {
    $this->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255',
    ]);

    // Verificar si el usuario ya existe, o en su caso, crearlo
    $user = User::firstOrCreate(
      ['email' => $this->email],
      ['name' => $this->name, 'password' => bcrypt(Str::random(16))]
    );

    // Verificar si el usuario ya fue invitado. Si es así, enviar un
    // correo con el mismo código de invitación. Si no, crear una nueva
    // invitación.
    $invitacion = Invitacion::firstOrCreate(
      [
        'evento_id' => $this->evento->id,
        'invitado_id' => $user->id
      ],
      [
        'invitante_id' => auth()->id(),
        'caduca' => now()->addDays(2),
        'codigo' => Str::random(32)
      ]
    );

    // Enviar la invitación por correo
    $user->notify(new NotificacionInvitacionAEvento($invitacion));

    // Refresh del evento para actualizar la lista de invitaciones
    $this->evento->refresh()->load('invitaciones.invitado');

    $this->success(
      title: '¡Invitación enviada!',
      description: 'La invitación ha sido enviada correctamente.',
      timeout: 3000,
      icon: 'fas.paper-plane'
    );
  }

  public function reenviarInvitacion(Invitacion $invitacion) {
    $invitacion->update(['caduca' => now()->addDays(2)]);
    $invitacion->save();
    $invitacion->invitado->notify(new NotificacionInvitacionAEvento($invitacion));

    $this->success(
      title: '¡Invitación reenviada!',
      description: 'La invitación ha sido reenviada correctamente.',
      timeout: 3000,
      icon: 'fas.paper-plane'
    );
  }

  public function confirmarCancelacion(Invitacion $invitacion) {
    $this->invitacion = $invitacion;
    $this->modalCancelacion = true;
  }

  public function cancelarInvitacion() {

    $this->invitacion = $this->invitacion->refresh();
    if ($this->invitacion->accepted_at) {
      $this->warning(
        title: 'El usuario ya había aceptado la invitación',
        description: 'Por favor revisa los participantes, y si quieres eliminar al usuario del evento, utiliza la opción correspondiente',
        timeout: 3000,
        icon: 'fas.circle-xmark'
      );
      $this->evento->refresh()->load('invitaciones.invitado');
      return;
    }

    $this->invitacion->delete();
    $this->evento->refresh()->load('invitaciones.invitado');

    $this->success(
      title: '¡Invitación cancelada!',
      description: 'La invitación ha sido cancelada correctamente.',
      timeout: 3000,
      icon: 'fas.circle-xmark'
    );

    $this->modalCancelacion = false;
  }

  public function confirmaEliminar(Invitacion $invitacion) {
    $this->invitacion = $invitacion;
    $this->modalEliminacion = true;
  }

  public function eliminarParticipante() {
    // Eliminar todas las participaciones del usuario en el evento
    $this->invitacion->delete();
    $this->evento->refresh()->load('invitaciones.invitado');

    Participacion::where('evento_id', $this->evento->id)
      ->where('user_id', $this->invitacion->invitado_id)
      ->delete();

    $this->success(
      title: '¡Participante eliminado!',
      description: 'El participante ha sido eliminado del evento correctamente.',
      timeout: 3000,
      icon: 'fas.circle-xmark'
    );

    $this->modalEliminacion = false;
  }

};
?>

<x-card class="bg-base-100">

  <x-modal
    title="¿Cancelar invitación?"
    wire:model="modalCancelacion"
    class="backdrop-blur-sm"
    >
    <div class="space-y-4">
      <p>Confirmas que quieres cancelar la invitación de <strong>{{ $invitacion?->invitado->name }}</strong> al este evento?</p>
      <p>Esta acción no se puede deshacer. Si cambias de opinion, tendrás que invitar nuevamente al participante.</p>
      <x-alert
        class="alert-warning"
        title="¡Atención!"
        description="Si el participante ya había aceptado la invitación, esta acción no eliminará su participación en el evento, revisa los detalles de los participantes."
        icon="fas.triangle-exclamation"
        />
      <div class="flex gap-1">
        <x-button
          class="btn-error uppercase tracking-widest"
          label="cancelar invitación"
          wire:click="cancelarInvitacion({{ $invitacion?->id }})"
          icon="fas.trash"
          spinner
          />
        <x-button
          class="btn-ghost btn-success uppercase tracking-widest"
          label="mantener"
          wire:click="$set('modalCancelacion', false)"
          icon="fas.circle-check"
          />
      </div>
    </div>
  </x-modal>

  <x-modal
    title="¿Eliminar participante?"
    wire:model="modalEliminacion"
    class="backdrop-blur-sm"
    >
    <div class="space-y-4">
      <p>Confirmas que quieres eliminar a <strong>{{ $invitacion?->invitado->name }}</strong> de este evento?</p>
      <p>La cuenta NO va a desaparecer del sistema, el usuario podrá seguir accediendo a otras funcionalidades y otros eventos. Esta acción únicamente lo eliminará de este evento.</p>
      <x-alert
        class="alert-error"
        title="¡Atención!"
        description="Esta acción no se puede deshacer."
        icon="fas.triangle-exclamation"
        />
      <div class="flex gap-1">
        <x-button
          class="btn-error uppercase tracking-widest"
          label="eliminar participante"
          wire:click="eliminarParticipante({{ $invitacion?->id }})"
          icon="fas.trash"
          spinner
          />
        <x-button
          class="btn-ghost btn-success uppercase tracking-widest"
          label="mantener"
          wire:click="$set('modalEliminacion', false)"
          icon="fas.circle-check"
          />
      </div>
    </div>
  </x-modal>


  <form wire:submit='invitar' class="flex gap-2 w-full">

    <div class="w-full">
      <x-input
        wire:model='name'
        class="outline-none! w-full"
        placeholder="Nombre del participante"
        label="Nombre del participante"
        required
        inline
        />
    </div>
    <div class="w-full">
      <x-input
        wire:model='email'
        class="outline-none! w-full"
        placeholder="Correo del participante"
        label="Correo del participante"
        required
        inline
        />
    </div>

    <x-button
      class="btn-primary"
      type="submit"
      icon="s-plus-circle"
      label="Invitar"
      spinner
      />

  </form>

  @if ($evento->invitaciones->isEmpty())
    <div class="p-4 text-center">
      No hay participantes aún.
    </div>
  @else
    <x-table
      :headers="$headers"
      :rows="$evento->invitaciones"
      >
      @scope("cell_invitado.name", $row)
        <div>{{ $row->invitado->name }}</div>
        <div class="text-xs text-base-content/50">{{ $row->invitado->email }}</div>
      @endscope

      @scope("cell_accepted_at", $row)
        <div>
          @if ($row->accepted_at)
            <x-badge
              class="badge-success tracking-widest"
              value="ACEPTADO"
              />
            <div>{{ $row->accepted_at->diffForHumans() }}</div>
          @elseif ($row->rejected_at)
            <x-badge
              class="badge-error tracking-widest"
              value="RECHAZADO"
              />
            <div>{{ $row->rejected_at->diffForHumans() }}</div>
          @else
            <x-badge
              class="badge-warning tracking-widest"
              value="PENDIENTE"
              />
          @endif
        </div>
      @endscope

      @scope("cell_created_at", $row)
        <div>
          {{ $row->created_at->diffForHumans() }}
        </div>
      @endscope

      @scope("actions", $row)
        <div class="flex gap-1">
          @if (!$row->accepted_at)
            <x-button
              class="btn-ghost btn-xs btn-success"
              icon="fas.paper-plane"
              wire:click="reenviarInvitacion({{ $row->id }})"
              tooltip-left="Reenviar invitación"
              spinner
              />
            <x-button
              class="btn-ghost btn-xs btn-error"
              icon="fas.circle-xmark"
              wire:click="confirmarCancelacion({{ $row->id }})"
              tooltip-left="Cancelar invitación"
              spinner
              />
          @else
            <x-button
              wire:click="confirmaEliminar({{ $row->id }})"
              class="btn-ghost btn-xs btn-error"
              icon="fas.circle-xmark"
              tooltip-left="Eliminar del Evento"
              spinner
              />
          @endif
        </div>
      @endscope
    </x-table>
  @endif


</x-card>
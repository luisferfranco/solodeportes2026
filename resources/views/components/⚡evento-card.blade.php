<?php

use App\Enums\EventoStatus;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  use Mary\Traits\Toast;

  public Evento $evento;

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }

  public function comprar() {
    $this->dispatch('open-modal-comprar', eventoId: $this->evento->id);
  }

  #[On('participacion-comprada')]
  public function actualizarParticipaciones($eventoId) {
    if ($this->evento->id != $eventoId) {
      return;
    }
    $this->evento = Evento::find($this->evento->id);
  }
};
?>

<div class="bg-base-100 rounded-lg shadow-xl overflow-hidden border border-gray-300 dark:border-gray-600 flex flex-col h-full">

  <livewire:modal-comprar-evento :evento="$evento" />

  <img src="{{ $evento->imagenUrl }}"
    alt="{{ $evento->temporada->deporte->nombre }}"
    class="w-full h-48 object-cover"
    >

  <div class="p-4 flex flex-col flex-1">
    <h2 class="text-xl text-accent uppercase tracking-wide font-bold">{{ $evento->nombre }}</h2>
    <p class="text-sm text-base-content/50 mb-2">{{ $evento->temporada->nombre }}</p>
    <div class="flex gap-2 mb-2">
      <x-badge value="{{ $evento->estado->label() }}" class="badge-sm badge-{{ $evento->estado->color() }} uppercase" />
      <x-badge value="{{ $evento->tipojuego->nombre }}" class="badge-sm badge-secondary font-bold uppercase tracking-wide" />
    </div>

    <p>Precio: ${{ Number::format($evento->precio, 2) }}</p>
    @if ($evento->participaciones->count() > 0)
      <p class="text-info py-1"><x-icon name="fas.people-group"/> {{ $evento->participaciones->count() }} Participantes</p>
    @endif

    <livewire:boletos-usuario :evento="$evento" />

    {{-- Botones de acción --}}
    <div class="flex gap-1 items-center justify-end mt-auto">
      <x-button
        link="{{ route('evento.show', $evento) }}"
        icon="fas.info-circle"
        label="Detalles"
        class="btn-ghost btn-info"
        />
      @if ($evento->estado === EventoStatus::ACTIVO)
        @if (auth()->user()->saldo >= $evento->precio)
          <x-button
            icon="fas.cart-shopping"
            class="btn-primary"
            label="Comprar"
            wire:click='comprar'
            spinner="comprar"
            />
        @else
          <x-button
            link="{{ route('banco.deposito') }}"
            icon="fas.credit-card"
            class="btn-error"
            label="Recargar saldo"
            />
        @endif
      @endif
    </div>
  </div>
</div>

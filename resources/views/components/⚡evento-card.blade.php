<?php

use App\Enums\EventoStatus;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $boletos;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->boletos = $evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }

  public function comprar() {
    $this->dispatch('open-modal-comprar');
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

    {{-- Boletos del usuario --}}
    <div class="mt-auto py-2">
      @if ($boletos->count() > 0)
        <p class="text-xs text-base-content/50 mb-1">Tienes {{ $boletos->count() }} boleto{{ $boletos->count() > 1 ? 's' : '' }}</p>
        @foreach ($boletos as $boleto)
          <div class="p-2 mb-2 bg-secondary/20 rounded-lg flex items-center gap-2">
            <x-icon name="fas.ticket" class="text-secondary" />
            <p class="text-sm text-secondary">#{{ sprintf("%05d",$boleto->id) }} {{ $boleto->nombre }}</p>
          </div>
        @endforeach
      @endif
    </div>

    {{-- Botones de acción --}}
    <div class="flex gap-1 items-center justify-end">
      <x-button
        link="{{ route(strtolower($evento->temporada->deporte->id) . '.' . $evento->tipojuego_id . '.show', $evento) }}"
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

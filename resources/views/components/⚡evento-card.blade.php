<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }
};
?>

<div class="bg-base-100 rounded-lg shadow-xl overflow-hidden border border-gray-300 dark:border-gray-600 flex flex-col h-full">

  <div class="relative">
    <img src="{{ $evento->imagenUrl }}"
    alt="{{ $evento->temporada->deporte->nombre }}"
    class="w-full h-48 object-cover"
    >
    <x-badge
      value="#Boletos Jugador"
      class="absolute top-1 left-1 badge-info badge-soft badge-xl" />
  </div>

  <div class="p-4 flex flex-col flex-1">
    <h2 class="text-xl text-accent uppercase tracking-wide font-bold">{{ $evento->nombre }}</h2>
    <p class="text-sm text-base-content/50">{{ $evento->temporada->nombre }}</p>
    <p>Precio: ${{ Number::format($evento->precio, 2) }}</p>
    <p><x-icon name="fas.people-group"/> # Participantes</p>


    <p class="my-4 text-base-content/80">{{ $evento->descripcion }}</p>

    <div class="mt-auto flex gap-1 items-center justify-end">
      <x-button
        link="#"
        icon="lucide.info"
        label="Detalles"
        class="btn-ghost btn-info"
        />
      @if (auth()->user()->saldo >= $evento->precio)
        <x-button
          link="#"
          icon="lucide.shopping-cart"
          class="btn-primary"
          label="Comprar"
          />
      @else
        <x-button
          link="{{ route('banco.deposito') }}"
          icon="lucide.credit-card"
          class="btn-error"
          label="Recargar saldo"
          />
      @endif
    </div>
  </div>
</div>

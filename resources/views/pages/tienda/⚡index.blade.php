<?php

use Livewire\Component;

new class extends Component
{
  public $eventos;

  public function mount() {
    $this->eventos = \App\Models\Evento::with('temporada.deporte')
      ->whereIn('estado', ['activo', 'pendiente'])
      ->where('ocultar', false)
      ->orderBy('created_at', 'desc')
      ->get();
  }
};
?>

<div>
  <x-title
    title="Tienda de eventos"
    subtitle="Compra eventos con tu saldo para participar en sus quinielas y ganar premios"
    icon="lucide.shopping-cart"
    />

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-stretch">
    @foreach ($eventos as $evento)
      <livewire:evento-card :evento="$evento" :key="'evid-'.$evento->id" />
    @endforeach
  </div>

</div>
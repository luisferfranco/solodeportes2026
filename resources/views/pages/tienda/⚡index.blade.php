<?php

use Livewire\Component;

new class extends Component
{
  public $eventos;

  public function mount() {
    $this->eventos = \App\Models\Evento::with('temporada.deporte')
      ->whereIn('estado', ['activo', 'pendiente'])
      ->orderBy('created_at', 'desc')
      ->get();
  }
};
?>

<div>
  <x-title title="Tienda de eventos" />

  <div class="grid grid-cols-3 gap-4 items-stretch">
    @foreach ($eventos as $evento)
      <livewire:evento-card :evento="$evento" :key="'evid-'.$evento->id" />
    @endforeach
  </div>

</div>
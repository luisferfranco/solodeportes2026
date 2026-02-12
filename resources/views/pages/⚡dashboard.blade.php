<?php

use Livewire\Component;

new class extends Component
{
  public $eventos;
  public $user;

  public function mount() {
    $this->user = auth()->user();

    // $this->eventos = $this->user->evento()::with('temporada.deporte')
    //   ->where('estado', 'activo')
    //   ->orderBy('created_at', 'desc')
    //   ->get();
  }
};
?>

<div>
  <x-title title="Mis Eventos" />

  <div class="grid grid-cols-3 gap-4 items-stretch">
    {{-- @foreach ($eventos as $evento)
      <livewire:evento-card :evento="$evento" :key="'evid-'.$evento->id" />
    @endforeach --}}
  </div>

</div>
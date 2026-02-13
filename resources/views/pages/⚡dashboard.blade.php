<?php

use Livewire\Component;

new class extends Component
{
  public $eventos;
  public $user;
  public $estados;
  public $toggle = false;

  public function mount() {
    $this->user = auth()->user();

    $this->eventos = $this->user->eventos()
      ->whereIn('estado', ['activo', 'encurso'])
      ->get();
  }

  public function updatedToggle($value) {
    if ($value) {
      $this->eventos = $this->user->eventos()
        ->whereIn('estado', ['activo', 'encurso', 'finalizado', 'archivado', 'pendiente'])
        ->get();
    } else {
      $this->eventos = $this->user->eventos()
        ->whereIn('estado', ['activo', 'encurso'])
        ->get();
    }

    foreach($this->eventos as $evento) {
      info($evento->nombre . ' - ' . $evento->estado->label());
    }
  }
};
?>

<div>
  <x-title title="Mis Eventos" />

  <x-toggle
    wire:model.live="toggle"
    label="Mostrar eventos terminados y futuros"
    />

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-stretch">
    @foreach ($eventos as $evento)
      <livewire:evento-card :evento="$evento" :key="'evid-'.$evento->id" />
    @endforeach
  </div>

</div>
<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public $participaciones;
  public $partId;

  public function mount(Evento $evento) {
    $this->participaciones = $evento->participaciones()
      ->where('user_id', auth()->id())
      ->with('user')
      ->get();

    $this->partId = request()->query('p')
      ?? $participacion?->id
      ?? $this->participaciones->first()?->id;
  }

  public function updatedPartId($partId) {
    $this->dispatch('participacion-seleccionada', $this->partId);
  }
};
?>

<x-select
  wire:model.live='partId'
  label="Selecciona tu participación"
  class="w-full outline-none! text-xl select-xl grow"
  :options="$participaciones"
  placeholder="Selecciona tu participación"
  option-label="nombre"
  option-value="id"
  spinner
  />

<?php

use App\Models\Evento;
use Mary\Traits\Toast;
use Livewire\Component;

new class extends Component
{
  use Toast;

  public $evento;
  public $estado;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->estado = $evento->estado;
  }

  public function updatedEstado($estado) {
    $this->evento->estado = $estado;
    $this->evento->save();
    $this->success('Estado actualizado');
  }
};
?>

<x-select
  wire:model.live='estado'
  :options="\App\Enums\EventoStatus::options()"
  option-label="name"
  option-value="id"
  class="outline-none! w-full p-0 border-0 text-center"
  placeholder="Seleccionar estado"
  />

<?php

use App\Models\Temporada;
use Livewire\Component;

new class extends Component
{
  public Temporada $temporada;
  public $ronda;
  public $options = [];

  public function mount(Temporada $temporada) {
    $this->temporada = $temporada;
    $this->ronda  = request()->query('rd') ?? $temporada->ronda;

    for ($i=1; $i<$temporada->rondafinal + 1; $i++) {
      $this->options[] = [
        'id' => $i,
        'name' => $i !== $temporada->rondafinal ? 'Ronda ' . $i : 'Eliminatoria Directa'
      ];
    }
  }

  public function updatedRonda($ronda) {
    $this->ronda = $ronda;
    $this->dispatch('ronda-seleccionada', $this->ronda);
  }
};
?>

<x-select
  wire:model.live='ronda'
  label="Selecciona la ronda"
  class="w-full outline-none! text-xl select-xl"
  :options="$options"
  />
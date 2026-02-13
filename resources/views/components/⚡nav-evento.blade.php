<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public $options;
  public $option;
  public Evento $evento;

  public function mount(Evento $evento, $opc = '1') {
    $this->evento = $evento;
    $this->option = $opc;
    $this->options = [
      ['id' => '1', 'name' => 'Información'],
      ['id' => '2', 'name' => 'Leaderboard'],
      ['id' => '3', 'name' => 'Pronósticos'],
      ['id' => '4', 'name' => 'Resultados'],
      ['id' => '5', 'name' => 'Marcadores'],
    ];
  }

  public function updatedOption($value) {
    info("Opción seleccionada: " . $value);

    $route = strtolower($this->evento->temporada->deporte_id) . '.' . $this->evento->tipojuego_id . '.';

    switch ($value) {
      case '1':
        $route .= "show";
        break;
      case '2':
        $route = 'dashboard';
        break;
      case '3':
        $route = 'dashboard';
        break;
      case '4':
        $route = 'dashboard';
        break;
      case '5':
        $route = 'dashboard';
        break;
    }

    info((route($route, ['evento' => $this->evento])));
    $this->redirectRoute($route, ['evento' => $this->evento]);
  }
};
?>

<x-select
  wire:model.live="option"
  placeholder="Selecciona una sección"
  :options="$options"
  class="outline-none text-xl select-xl mb-4"
  icon="fas.location-arrow"
  />
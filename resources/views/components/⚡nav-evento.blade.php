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

    $part = $evento->participaciones()
      ->where('user_id', auth()->id())
      ->count();

    if ($part > 0) {
      $this->options = [
        ['id' => '1', 'name' => 'Información'],
        ['id' => '2', 'name' => 'Leaderboard'],
        ['id' => '3', 'name' => 'Pronósticos'],
        ['id' => '4', 'name' => 'Resultados'],
        ['id' => '5', 'name' => 'Marcadores'],
      ];
    } else {
      $this->options = [
        ['id' => '1', 'name' => 'Información'],
        ['id' => '5', 'name' => 'Marcadores'],
      ];
    }
  }

  public function updatedOption($value) {
    info("Opción seleccionada: " . $value);

    $route = strtolower($this->evento->temporada->deporte_id) . '.' . $this->evento->tipojuego_id . '.';

    // 1. show
    // 2. leaderboard
    // 3. pronosticos
    // 4. resultados
    // 5. marcadores

    $routeMap = [
      '1' => 'show',
      '2' => 'leaderboard',
      '3' => 'pronosticos',
      '4' => 'resultados',
      '5' => 'marcadores',
    ];

    $route .= $routeMap[$value] ?? 'show';

    info(route($route, ['evento' => $this->evento]));
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
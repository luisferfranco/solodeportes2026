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
    if (!$value) return;

    $route = strtolower($this->evento->temporada->deporte_id) . '.' . $this->evento->tipojuego_id . '.';

    $routeMap = [
      '1' => 'evento.show',
      '2' => 'evento.leaderboard',
      '3' => $route . 'pronosticos',
      '4' => 'evento.resultados',
      '5' => 'evento.marcadores',
    ];

    $this->redirectRoute($routeMap[$value ?? 1], ['evento' => $this->evento]);
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
<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $juegos;
  public $ronda = 1;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->juegos = $evento
      ->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->with(['homeTeam', 'awayTeam'])
      ->get();
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Pronósticos" />

  <p>Selector de Jornadas</p>

  <div class="max-w-3xl mx-auto">
    @foreach ($juegos as $juego)
      <livewire:pronostico-juego :juego="$juego" :key="'juego-' . $juego->id" />
    @endforeach
  </div>
</div>
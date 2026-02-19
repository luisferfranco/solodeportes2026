<?php

use App\Models\Evento;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $rd;
  public $participaciones;

  public function mount(Evento $evento)
  {
    $this->evento = $evento;
    $this->rd = request()->get('rd') ?? $this->evento->temporada->ronda;

    $juegos = $evento->temporada->juegos()
      ->where('ronda', $this->rd)
      ->pluck('id')
      ->toArray();

    $aciertos     = $evento->acierto;
    $diferencias  = $evento->diferencia;
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->redirectRoute('fb.qn.leaderboard', ['evento' => $this->evento->id, 'rd' => $ronda]);
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Leaderboard" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="2" />

  <livewire:selector-rondas :temporada="$evento->temporada" :key="'selector-ronda-' . $evento->id" />

 {{ $participaciones }}
</div>
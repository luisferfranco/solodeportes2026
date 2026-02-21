<?php

use App\Models\Evento;
use App\Models\Participacion;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $juegos;
  public $ronda;
  public $participaciones, $participacion;
  public $partId;

  public function mount(Evento $evento, Participacion $participacion = null) {
    if (Gate::forUser(auth()->user())->denies('view', $evento)) {
      $this->redirectRoute('evento.show', ['evento' => $evento]);
      return;
    }

    $this->evento = $evento;
    $this->participaciones = $evento->participaciones()
      ->where('user_id', auth()->id())
      ->with('user')
      ->get();

    $this->partId = request()->query('p')
      ?? $participacion?->id
      ?? $this->participaciones->first()?->id;

    $this->participacion = $this->participaciones->firstWhere('id', $this->partId);

    $this->ronda  = request()->query('rd') ?? $evento->temporada->ronda;

    $this->juegos = $this->evento
      ->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->with(['homeTeam', 'awayTeam'])
      ->get();
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->ronda = $ronda;
    $this->redirectRoute('fb.qn.pronosticos', ['evento' => $this->evento->id, 'rd' => $this->ronda, "p" => $this->partId]);
  }

  #[On('participacion-seleccionada')]
  public function actualizaParticipacion($participacionId) {
    $this->redirectRoute('fb.qn.pronosticos', ['evento' => $this->evento->id, 'rd' => $this->ronda, "p" => $participacionId]);
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Pronósticos" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="3" />

  @if ($participaciones->count() > 1)
    <livewire:selector-participacion :evento="$evento" :key="'selector-participacion-' . $evento->id" />
  @endif

  <livewire:selector-rondas :temporada="$evento->temporada" />

  <div class="max-w-3xl mx-auto">
    @foreach ($juegos as $juego)
      <livewire:pronostico-juego :juego="$juego" :participacion="$participacion" :key="'juego-' . $juego->id" />
    @endforeach
  </div>
</div>
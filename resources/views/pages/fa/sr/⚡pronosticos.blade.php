<?php

use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Survivor;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $juegos;
  public $ronda;
  public $participaciones, $participacion;
  public $partId;
  public $seleccionado = null;
  public $seleccionadoId = null;
  public $estadoJugador = 'vivo';
  public $historico = [];

  public function mount(Evento $evento, ?Participacion $participacion = null) {
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
    $this->ronda = min((int) (request()->query('rd') ?? $evento->temporada->ronda), (int) $evento->temporada->ronda);

    $this->juegos = $this->evento
      ->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->with(['homeTeam', 'awayTeam'])
      ->orderBy('valido_hasta')
      ->get();

    $this->cargarEstadoJugador();
    $this->historico = Survivor::query()
      ->where('participacion_id', $this->participacion?->id)
      ->where('ronda', '<', $this->ronda)
      ->with('equipo')
      ->orderBy('ronda')
      ->get();
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->ronda = min((int) $ronda, (int) $this->evento->temporada->ronda);
    $this->redirectRoute('fa.sr.pronosticos', ['evento' => $this->evento, 'rd' => $this->ronda, 'p' => $this->partId]);
  }

  #[On('participacion-seleccionada')]
  public function actualizaParticipacion($participacionId) {
    $this->redirectRoute('fa.sr.pronosticos', ['evento' => $this->evento, 'rd' => $this->ronda, 'p' => $participacionId]);
  }

  public function seleccionarEquipo(int $juegoId, int $equipoId): void
  {
    if (! $this->participacion || $this->estadoJugador === 'muerto') {
      return;
    }

    $juego = $this->evento->temporada->juegos()->whereKey($juegoId)->first();

    if (! $juego || $juego->ronda !== $this->ronda) {
      return;
    }

    if ($juego->valido_hasta && $juego->valido_hasta->isPast()) {
      return;
    }

    $actual = Survivor::query()
      ->where('participacion_id', $this->participacion->id)
      ->where('ronda', $this->ronda)
      ->first();

    if ($actual && $actual->equipo_id === $equipoId) {
      $this->seleccionadoId = $equipoId;
      $this->seleccionado = $actual;

      return;
    }

    $this->seleccionado = Survivor::query()->updateOrCreate([
      'participacion_id' => $this->participacion->id,
      'ronda' => $this->ronda,
    ], [
      'equipo_id' => $equipoId,
      'acierto' => 0,
    ]);

    $this->seleccionadoId = $this->seleccionado->equipo_id;
  }

  public function estadoEquipo(int $equipoId, int $ronda): ?string
  {
    if (! $this->participacion || $ronda >= $this->evento->temporada->ronda) {
      return null;
    }

    $seleccion = Survivor::query()
      ->where('participacion_id', $this->participacion->id)
      ->where('ronda', $ronda)
      ->where('equipo_id', $equipoId)
      ->first();

    if (! $seleccion) {
      return null;
    }

    return $seleccion->acierto ? 'success' : 'error';
  }

  protected function cargarEstadoJugador(): void
  {
    $this->seleccionado = Survivor::query()
      ->where('participacion_id', $this->participacion?->id)
      ->where('ronda', $this->ronda)
      ->first();

    $this->seleccionadoId = $this->seleccionado?->equipo_id;
    $this->estadoJugador = 'vivo';

    if (! $this->participacion) {
      $this->estadoJugador = 'muerto';

      return;
    }

    $previas = Survivor::query()
      ->where('participacion_id', $this->participacion->id)
      ->where('ronda', '<', $this->ronda)
      ->get();

    if ($previas->isNotEmpty() && $previas->contains(fn (Survivor $seleccion) => ! (bool) $seleccion->acierto)) {
      $this->estadoJugador = 'muerto';
    }
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Pronósticos" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="3" />

  @if ($participaciones->count() > 1)
    <livewire:selector-participacion :evento="$evento" :key="'selector-participacion-' . $evento->id" />
  @endif

  <livewire:selector-rondas :model="$evento" />

  @if ($participacion)
    <div class="max-w-3xl mx-auto mt-4">
      <div class="alert {{ $estadoJugador === 'vivo' ? 'alert-success' : 'alert-error' }} shadow-sm mb-6">
        <span>{{ $estadoJugador === 'vivo' ? 'Vivo' : 'Muerto' }}</span>
      </div>
    </div>
  @endif

  <div class="max-w-3xl mx-auto mt-6">
    <div class="grid grid-cols-2 gap-2">
      @foreach ($juegos as $juego)
        @php
          $estadoEquipoAway = $this->estadoEquipo($juego->awayTeam->id, $juego->ronda);
          $estadoEquipoHome = $this->estadoEquipo($juego->homeTeam->id, $juego->ronda);
        @endphp

        <x-sr-equipo
          :equipo="$juego->awayTeam"
          :seleccionado="$seleccionadoId"
          :estado="$estadoEquipoAway"
          :disabled="$estadoJugador !== 'vivo' || ($juego->valido_hasta && $juego->valido_hasta->isPast())"
          wire:click="seleccionarEquipo({{ $juego->id }}, {{ $juego->awayTeam->id }})"
          />

        <x-sr-equipo
          :equipo="$juego->homeTeam"
          :seleccionado="$seleccionadoId"
          :estado="$estadoEquipoHome"
          :disabled="$estadoJugador !== 'vivo' || ($juego->valido_hasta && $juego->valido_hasta->isPast())"
          wire:click="seleccionarEquipo({{ $juego->id }}, {{ $juego->homeTeam->id }})"
          />
      @endforeach
    </div>
  </div>

  @if ($historico->isNotEmpty())
    <div class="max-w-3xl mx-auto mt-8">
      <h3 class="text-lg font-semibold mb-3">Juegos ya jugados</h3>
      <div class="space-y-2">
        @foreach ($historico as $registro)
          <div class="flex items-center justify-between rounded-lg border border-base-300 bg-base-100 px-3 py-2">
            <span class="font-medium">Ronda {{ $registro->ronda }}</span>
            <span>{{ $registro->equipo?->nombre ?? 'Equipo' }}</span>
            <span class="badge {{ $registro->acierto ? 'badge-success' : 'badge-error' }}">
              {{ $registro->acierto ? 'Acierto' : 'Fallo' }}
            </span>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
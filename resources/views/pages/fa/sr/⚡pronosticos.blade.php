<?php

use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Survivor;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $juegos;
  public $ronda;
  public $participaciones, $participacion;
  public $partId;
  public $seleccionado = null;
  public $seleccionadoId = null;
  public $estadoJugador = 'vivo';
  public $historico = [];
  public array $estadoEquipos = [];

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
    $this->ronda = (int) (request()->query('rd') ?? $evento->temporada->ronda);

    $this->juegos = $this->evento
      ->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->with(['homeTeam', 'awayTeam'])
      ->orderBy('valido_hasta')
      ->get();

    $this->cargarEstadoJugador();
    $this->cargarEstadoEquipos();
    $this->historico = Survivor::query()
      ->where('participacion_id', $this->participacion?->id)
      ->where('ronda', '<', $this->ronda)
      ->with('equipo')
      ->orderBy('ronda')
      ->get();
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->ronda = (int) $ronda;
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

    $primerJuego = $this->juegos->first();

    // Si el primer partido de la ronda actual aún está abierto, no permitimos cambiar la selección y dejamos el equipo actual intacto.
    if ($primerJuego?->valido_hasta && now()->gt($primerJuego->valido_hasta)) {
      $this->error(
        title:  'No intentes hacer trampas',
        description: 'No puedes cambiar tu selección una vez que ha empezado el primer partido de la ronda',
        icon: 'fas.circle-exclamation',
        timeout: 5000,
      );
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

  public function estadoEquipo(int $equipoId): ?string
  {
    if (! $this->participacion) {
      return null;
    }

    return $this->estadoEquipos[$equipoId] ?? null;
  }

  protected function cargarEstadoEquipos(): void
  {
    $this->estadoEquipos = [];

    if (! $this->participacion) {
      return;
    }

    $temporadaRonda = (int) $this->evento->temporada->ronda;
    $query = Survivor::query()
      ->where('participacion_id', $this->participacion->id)
      ->orderBy('ronda');

    if ($this->ronda < $temporadaRonda) {
      $query->where('ronda', $this->ronda);
    } else {
      $query->where('ronda', '<', $this->ronda);
    }

    $selecciones = $query->get(['equipo_id', 'acierto']);

    foreach ($selecciones as $seleccion) {
      if (! $seleccion->equipo_id) {
        continue;
      }

      $this->estadoEquipos[(int) $seleccion->equipo_id] = $seleccion->acierto ? 'success' : 'error';
    }
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

  <livewire:selector-rondas :model="$evento" :ronda="$ronda" :key="'selector-ronda-' . $evento->id" />

  @if ($historico->isNotEmpty())
    <div class="max-w-3xl mx-auto mt-8">
      <h3 class="text-lg font-semibold mb-3">Equipos ya Usados</h3>
      <div class="flex gap-4 border-base-300 bg-base-100 rounded-xl p-4">
        @foreach ($historico as $registro)
          <div class="relative">
            <img
              src="{{ $registro->equipo?->logo }}"
              alt="{{ $registro->equipo?->nombre }}"
              class="w-8 h-8 object-contain"
              />
            <x-icon
              name="{{ $registro->acierto ? 'fas.circle-check' : 'fas.circle-xmark' }}"
              class="absolute -top-1 -right-2 w-4 h-4 {{ $registro->acierto ? 'text-success' : 'text-error' }}"
              />
          </div>
        @endforeach
      </div>
    </div>
  @endif

  @if ($participacion)
    <div class="max-w-3xl mx-auto mt-4">
      <x-alert
        class="{{ $estadoJugador === 'vivo' ? 'alert-success' : 'alert-error' }}"
        title="{{ $estadoJugador === 'vivo' ? 'Sobreviviente' : 'Has muerto' }}"
        icon="{{ $estadoJugador === 'vivo' ? 'fas.shield-halved' : 'fas.skull-crossbones' }}"
        />
    </div>
  @endif

  <div class="max-w-3xl mx-auto mt-6">
    <div class="grid grid-cols-2 gap-2">
      @foreach ($juegos as $juego)
        @php
          $estadoEquipoAway = $this->estadoEquipo($juego->awayTeam->id);
          $estadoEquipoHome = $this->estadoEquipo($juego->homeTeam->id);
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
</div>
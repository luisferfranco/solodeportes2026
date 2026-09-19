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
  public $estadoJugador = null;
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
    if (! $this->participacion || $this->estadoJugador === false) {
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

    $previos = $this->historico->pluck('equipo_id')->toArray();
    if (in_array($equipoId, $previos)) {
      $this->error(
        title:  'Selección inválida',
        description: 'No puedes seleccionar un equipo que ya has elegido en rondas anteriores',
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
      'acierto' => null,
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

      $this->estadoEquipos[(int) $seleccion->equipo_id] = match (true) {
        $seleccion->acierto === null => 'warning',
        $seleccion->acierto === true => 'success',
        default => 'error',
      };
    }
  }

  protected function cargarEstadoJugador(): void
  {
    $this->seleccionado = Survivor::query()
      ->where('participacion_id', $this->participacion?->id)
      ->where('ronda', $this->ronda)
      ->first();

    $this->seleccionadoId = $this->seleccionado?->equipo_id;

    $aciertoRaw = $this->seleccionado?->getRawOriginal('acierto');
    $this->estadoJugador = match (true) {
      $aciertoRaw === null => null,
      (bool) $aciertoRaw === true => true,
      default => false,
    };

    if (! $this->participacion) {
      return;
    }

    if ($this->ronda > 1) {
      $previas = Survivor::query()
        ->where('participacion_id', $this->participacion->id)
        ->where('ronda', '<', $this->ronda)
        ->get();

      if ($previas->isNotEmpty() && $previas->contains(fn (Survivor $registro) => ! (bool) $registro->acierto)) {
        $this->estadoJugador = false;
      }
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
              name="{{ $registro->acierto === null ? 'fas.circle-exclamation' : ($registro->acierto ? 'fas.circle-check' : 'fas.circle-xmark') }}"
              class="absolute -top-1 -right-2 w-4 h-4 {{ $registro->acierto === null ? 'text-warning' : ($registro->acierto ? 'text-success' : 'text-error') }}"
              />
          </div>
        @endforeach
      </div>
    </div>
  @endif

  @if ($this->ronda === $evento->temporada->ronda && $participacion)
    <div class="max-w-3xl mx-auto mt-4">
      @if ($estadoJugador === null)
        <x-alert
          class="alert-warning"
          title="Esperando a tu destino"
          icon="fas.hourglass-half"
        />
      @elseif ($estadoJugador === true)
        <x-alert
          class="alert-success"
          title="Sobreviviente"
          icon="fas.shield-halved"
        />
      @else
        <x-alert
          class="alert-error"
          title="Moriste"
          icon="fas.skull-crossbones"
        />
      @endif
    </div>
  @endif

  @if ($this->ronda > $evento->temporada->ronda)
    <div class="max-w-3xl mx-auto mt-6">
      <div class="alert alert-neutral">
        <div class="flex items-center gap-3">
          <i class="fas fa-clock text-xl"></i>
          <span>Esta ronda aún no está abierta</span>
        </div>
      </div>
    </div>
  @elseif ($this->ronda === $evento->temporada->ronda && $estadoJugador !== false)
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
            :disabled="$estadoJugador === false || ($juego->valido_hasta && $juego->valido_hasta->isPast())"
            wire:click="seleccionarEquipo({{ $juego->id }}, {{ $juego->awayTeam->id }})"
          />

          <x-sr-equipo
            :equipo="$juego->homeTeam"
            :seleccionado="$seleccionadoId"
            :estado="$estadoEquipoHome"
            :disabled="$estadoJugador === false || ($juego->valido_hasta && $juego->valido_hasta->isPast())"
            wire:click="seleccionarEquipo({{ $juego->id }}, {{ $juego->homeTeam->id }})"
          />
        @endforeach
      </div>
    </div>
  @endif
</div>
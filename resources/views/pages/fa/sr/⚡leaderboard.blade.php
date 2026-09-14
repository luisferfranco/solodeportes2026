<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Survivor;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
  public Evento $evento;
  public int $ronda;
  public array $columnas = [];
  public int $maxFilas = 0;
  public bool $mostrarNombres = true;
  public ?string $fechaInicioRonda = null;

  public function mount(Evento $evento) {
    if (Gate::forUser(auth()->user())->denies('view', $evento)) {
      $this->redirectRoute('evento.show', ['evento' => $evento]);
      return;
    }

    $this->evento = $evento;
    $this->ronda = (int) (request()->query('rd') ?? $evento->temporada->ronda);

    $this->cargarLeaderboard();
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->ronda = (int) $ronda;
    $this->redirectRoute('fa.sr.leaderboard', ['evento' => $this->evento, 'rd' => $this->ronda]);
  }

  protected function cargarLeaderboard(): void
  {
    $this->resolverModoVisualizacion();

    $selecciones = Survivor::query()
      ->where('ronda', $this->ronda)
      ->whereHas('participacion', fn ($query) => $query->where('evento_id', $this->evento->id))
      ->with(['participacion:id,nombre', 'equipo:id,nombre,logo'])
      ->orderBy('equipo_id')
      ->get();

    $juegosRonda = $this->evento->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->get();

    $estadoPorAcierto = function ($equipoId) use ($juegosRonda): string {
      $juegoEquipo = $juegosRonda->first(function ($juego) use ($equipoId) {
        return (int) $juego->home_id === (int) $equipoId || (int) $juego->away_id === (int) $equipoId;
      });

      // 'warning' si el resultado no es FT o AOT
      if (! $juegoEquipo || ! in_array(strtoupper((string) ($juegoEquipo->status ?? '')), ['FT', 'AOT'])) {
        return 'warning';
      }

      $homeScore = (int) ($juegoEquipo->home_score ?? 0);
      $awayScore = (int) ($juegoEquipo->away_score ?? 0);

      if ($homeScore === $awayScore) {
        return 'error';
      }

      $equipoGanadorId = $homeScore > $awayScore
        ? (int) $juegoEquipo->home_id
        : (int) $juegoEquipo->away_id;

      return (int) $equipoId === $equipoGanadorId ? 'success' : 'error';
    };

    $this->columnas = $selecciones
      ->groupBy('equipo_id')
      ->map(function ($items, $equipoId) use ($estadoPorAcierto) {
        $primero = $items->first();

        return [
          'equipo_id' => (int) $equipoId,
          'equipo_nombre' => $primero?->equipo?->nombre ?? 'Equipo',
          'equipo_logo' => $primero?->equipo?->logo,
          'estado' => $estadoPorAcierto($equipoId),
          'total' => $items->count(),
          'participaciones' => $items
            ->pluck('participacion.nombre')
            ->filter()
            ->values()
            ->all(),
        ];
      })
      ->sortKeys()
      ->values()
      ->all();

    if (! $this->mostrarNombres) {
      $this->maxFilas = empty($this->columnas) ? 0 : 1;

      return;
    }

    $this->maxFilas = (int) collect($this->columnas)
      ->map(fn ($columna) => count($columna['participaciones']))
      ->max();
  }

  protected function resolverModoVisualizacion(): void
  {
    $fechaInicio = $this->evento->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->min('valido_hasta');

    if (! $fechaInicio) {
      $this->fechaInicioRonda = null;
      $this->mostrarNombres = true;

      return;
    }

    $inicio = Carbon::parse($fechaInicio);
    $this->fechaInicioRonda = $inicio->toDateTimeString();
    $this->mostrarNombres = now()->greaterThan($inicio);
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Leaderboard Survivor" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="2" />
  <livewire:selector-rondas :model="$evento" :ronda="$ronda" :key="'selector-ronda-' . $evento->id" />

  @if ($fechaInicioRonda)
    <p class="text-sm opacity-70 mb-4">
      Inicio de ronda: {{ $fechaInicioRonda }}
    </p>
  @endif

  @if (empty($columnas))
    <x-alert
      title="Sin pronósticos"
      description="No hay selecciones de survivor para la ronda {{ $ronda }}."
      icon="fas.circle-info"
      class="alert-info"
      />
  @else
    @php
      $estadoHeader = [
        'warning' => 'bg-warning/50 border-warning/40',
        'success' => 'bg-success/50 border-success/40',
        'error' => 'bg-error/50 border-error/40',
        'neutral' => 'bg-base-200 border-base-300',
      ];
    @endphp

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
      @foreach ($columnas as $columna)
        <article class="rounded-xl border border-base-300 bg-base-100 shadow-sm overflow-hidden">
          <header class="{{ $estadoHeader[$columna['estado']] ?? $estadoHeader['neutral'] }} border-b border-base-300 p-3">
            <div class="flex items-center gap-3">
              @if ($columna['equipo_logo'])
                <img
                  src="{{ $columna['equipo_logo'] }}"
                  alt="{{ $columna['equipo_nombre'] }}"
                  class="w-9 h-9 object-contain"
                  />
              @endif
              <p class="font-semibold leading-tight">{{ $columna['equipo_nombre'] }}</p>
            </div>
          </header>

          <div class="p-3">
            @if ($mostrarNombres)
              <ul class="space-y-2">
                @forelse ($columna['participaciones'] as $participacionNombre)
                  <li class="min-h-6 border-b border-base-200 pb-1 last:border-b-0 last:pb-0">{{ $participacionNombre }}</li>
                @empty
                  <li class="opacity-70">Sin participaciones</li>
                @endforelse
              </ul>
            @else
              <p class="font-semibold">{{ $columna['total'] }} pronóstico(s)</p>
            @endif
          </div>
        </article>
      @endforeach
    </div>
  @endif

</div>
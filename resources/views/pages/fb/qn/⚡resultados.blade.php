<?php

use App\Models\Evento;
use App\Models\Juego;
use App\Models\Participacion;
use App\Models\Pronostico;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $pronosticos;
  public $participacion;
  public $rd;
  public $juegos;
  public $juegosIds;
  public $headers;
  public $nopar;
  public $resSum;
  public $difSum;

  public function mount(Evento $evento)
  {
    $this->evento = $evento;
    $this->nopar = $evento->participaciones()->where('user_id', auth()->id())->count();
    $this->participacion = Participacion::where('user_id', auth()->id())
          ->where('evento_id', $this->evento->id)
          ->first();

    $this->rd = request()->get('rd') ?? $this->participacion->evento->temporada->ronda;

    $temporada = $this->evento->temporada;

    // para desplegar los resultados, deberán traerse todos los juegos de la ronda y hacer un outer join con los pronósticos, para mostrar los juegos aunque no se hayan pronosticado. El resultado deberá guardarse en la variable $this->pronosticos, que se usará en la vista para mostrar los resultados.
    $this->pronosticos = Juego::where('temporada_id', $temporada->id)
      ->where('ronda', $this->rd)
      ->with(['homeTeam', 'awayTeam', 'pronosticos' => function ($query) {
        $query->where('participacion_id', $this->participacion->id);
      }])
      ->get();

    // Obtener la suma de los pronosticos en los campos res y dif
    $this->resSum = $this->pronosticos->sum(function ($juego) {
      return $juego->pronosticos->first()?->res ?? 0;
    });
    $this->difSum = $this->pronosticos->sum(function ($juego) {
      return $juego->pronosticos->first()?->dif ?? 0;
    });

    $this->headers = [
      ['key' => 'juegos', 'label' => 'Juego'],
      ['key' => 'marcador', 'label' => 'Marcador', 'class' => 'text-right'],
      ['key' => 'pronostico', 'label' => 'Pronóstico', 'class' => 'text-center'],
      ['key' => 'real', 'label' => 'Real', 'class' => 'text-center'],
      ['key' => 'puntos', 'label' => 'Puntos', 'class' => 'text-center'],
    ];
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    info($ronda);
    $this->redirectRoute('fb.qn.resultados', ['evento' => $this->evento->id, 'rd' => $ronda]);
  }

  #[On('participacion-seleccionada')]
  public function actualizarParticipacion($partId) {
    info($partId);
    $this->redirectRoute('fb.qn.resultados', ['evento' => $this->evento->id, 'p' => $partId, 'rd' => $this->rd]);
  }

  #[On('marcadores-cargados')]
  public function actualizarMarcadores($ronda) {
    $this->mount($this->evento);
  }
}
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Resultados para {{ $participacion->nombre }}" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="4" />

  @if ($nopar > 1)
    <livewire:selector-participacion :evento="$evento" :key="'selector-participacion-' . $evento->id" />
  @endif

  <livewire:selector-rondas :temporada="$evento->temporada" :key="'selector-ronda-' . $evento->id" />

  <div class="flex justify-end items-center mt-4 gap-4">
    <x-stat
      title="Aciertos"
      value="{{ $resSum }}"
      icon="fas.check"
      class="bg-info/30"
      />
    <x-stat
      title="Diferencias"
      value="{{ $difSum }}"
      icon="fas.bullseye"
      class="bg-info/30"
      />
    <x-stat
      title="Total"
      value="{{ $resSum * $evento->acierto + $difSum * $evento->diferencia }}"
      icon="fas.star"
      class="bg-info/70"
      />
  </div>

  <x-table
    :headers="$headers"
    :rows="$pronosticos"
    class="mt-6"
    >
    @scope('cell_juegos', $row)
      <div class="flex flex-col space-y-2">
        <div>
          <span class="text-xs text-base-content/50">{{ $row->valido_hasta->format('d M Y, H:i') }}</span>
        </div>
        <div class="flex items-center gap-2">
          <img src="{{ $row->homeTeam->logo }}" alt="{{ $row->homeTeam->nombre }}" class="w-6 h-6">
          <span>{{ $row->homeTeam->nombre }}</span>
        </div>
        <div class="flex items-center gap-2">
          <img src="{{ $row->awayTeam->logo }}" alt="{{ $row->awayTeam->nombre }}" class="w-6 h-6">
          <span>{{ $row->awayTeam->nombre }}</span>
        </div>
      </div>
    @endscope

    @scope('cell_marcador', $row)
      <div class="flex flex-col space-y-2">
        <div class="flex items-center justify-end gap-2">
          <span>{{ $row->home_score ?? '???' }}</span>
        </div>
        <div class="flex items-center justify-end gap-2">
          <span>{{ $row->away_score ?? '???' }}</span>
        </div>
      </div>
    @endscope

    @scope('cell_pronostico', $row)
      <div class="text-2xl text-center font-bold">
        {{ $row->pronosticos->first()?->diferencia ?? '???' }}
      </div>
    @endscope

    @scope('cell_real', $row)
      @if ($row->home_score === null || $row->away_score === null)
        <x-badge class="badge-info badge-xs" value="SIN RESULTADO" />
      @else
        <div class="text-2xl text-center font-bold">
          {{ $row->home_score - $row->away_score }}
        </div>
      @endif
    @endscope

    @scope('cell_puntos', $row)
      <div class="text-center">
        @if ($row->pronosticos->first()?->diferencia === null)
          <x-badge class="badge-info" value="NO PICK" />
        @elseif ($row->pronosticos->first()?->res === null)
          <x-badge class="badge-info badge-xs" value="SIN CALIFICAR" />
        @elseif ($row->pronosticos->first()?->res == 0)
          <x-icon name="fas.thumbs-down" class="text-error h-8 w-8" />
          <p><x-badge class="badge-error text-error-content not-last:badge-xs" value="FALLIDO" /></p>
        @else
          @if ($row->pronosticos->first()?->dif == 1)
            <x-icon name="fas.thumbs-up" class="text-success h-8 w-8" />
            <p><x-badge class="badge-success badge-xs" value="TOTAL" /></p>
          @else
            <x-icon name="fas.thumbs-up" class="text-warning h-8 w-8" />
            <p><x-badge class="badge-warning badge-xs" value="PARCIAL" /></p>
          @endif
        @endif
      </div>
    @endscope

  </x-table>
</div>
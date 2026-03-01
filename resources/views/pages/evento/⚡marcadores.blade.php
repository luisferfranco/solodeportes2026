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
    $this->rd = request()->get('rd') ?? $this->evento->temporada->ronda;

    $temporada = $this->evento->temporada;


    $this->juegos = Juego::where('temporada_id', $temporada->id)
      ->where('ronda', $this->rd)
      ->with(['homeTeam', 'awayTeam'])
      ->get();

    $this->headers = [
      ['key' => 'juegos', 'label' => 'Juego'],
      ['key' => 'marcador', 'label' => 'Marcador', 'class' => 'text-right'],
      ['key' => 'youtube', 'label' => 'YouTube', 'class' => 'text-center'],
    ];
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->redirectRoute('fb.qn.resultados', ['evento' => $this->evento, 'rd' => $ronda]);
  }

  #[On('marcadores-cargados')]
  public function actualizarMarcadores($ronda) {
    $this->mount($this->evento);
  }
}
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Marcadores" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="5" />

  <livewire:selector-rondas :temporada="$evento->temporada" :key="'selector-ronda-' . $evento->id" />

  <div class="max-w-3xl mx-auto">
    <x-table
      :headers="$headers"
      :rows="$juegos"
      class="mt-6"
      >
      @scope('cell_juegos', $row)
        <div class="flex flex-col space-y-2">
          <div class="text-xs text-base-content/50">
            {{ $row->valido_hasta->format('d M Y, H:i') }}
            <x-badge value="{{ $row->status }}" class="badge-neutral badge-xs ml-2" />
          </div>
          <div class="flex items-center gap-2">
            <img src="{{ $row->homeTeam->logo }}" alt="{{ $row->homeTeam->nombre }}" class="w-6 h-6">
            <span class="{{ $row->home_score > $row->away_score ? 'font-bold' : '' }}">{{ $row->homeTeam->nombre }}</span>
          </div>
          <div class="flex items-center gap-2">
            <img src="{{ $row->awayTeam->logo }}" alt="{{ $row->awayTeam->nombre }}" class="w-6 h-6">
            <span class="{{ $row->away_score > $row->home_score ? 'font-bold' : '' }}">{{ $row->awayTeam->nombre }}</span>
          </div>
        </div>
      @endscope

      @scope('cell_marcador', $row)
        <div class="flex flex-col space-y-2">
          <div class="flex items-center justify-end gap-2">
            <span class="{{ $row->home_score > $row->away_score ? 'font-bold' : '' }}">{{ $row->home_score ?? '???' }}</span>
          </div>
          <div class="flex items-center justify-end gap-2">
            <span class="{{ $row->away_score > $row->home_score ? 'font-bold' : '' }}">{{ $row->away_score ?? '???' }}</span>
          </div>
        </div>
      @endscope

      @scope('cell_youtube', $row)
        @if($row->youtube)
          <a href="{{ $row->youtube }}" target="_blank" class="text-blue-500 hover:underline">
            <x-icon name="fab.youtube" class="w-10 h-10 text-error" />
          </a>
        @endif
      @endscope

    </x-table>
  </div>
</div>
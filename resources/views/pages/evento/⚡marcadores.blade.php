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
      ->orderBy('valido_hasta')
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

    @foreach ($juegos as $j)
      <div class="bg-base-300 rounded-t shadow-md px-2 py-1">
        <span class="text-xs text-base-content/50">{{ $j->valido_hasta->format('d M Y, H:i') }}</span>
        <x-badge class="badge-info badge-xs" value="{{ $j->status }}" />
      </div>

      <div class="grid grid-cols-5 text-xs mb-2 bg-base-100 rounded-b shadow-md px-2 py-1 border border-base-300">
        <div class="col-span-3 space-y-2">
          {{-- Home --}}
          <div class="flex justify-between items-center gap-1">
            <div class="flex gap-1">
              <img src="{{ $j->homeTeam->logo }}" alt="{{ $j->homeTeam->nombre }}" class="w-6 h-6">
              <span class="font-medium text-sm">{{ $j->homeTeam->nombre }}</span>
            </div>
            <div>
              {{ $j->home_score ?? "???" }}
            </div>
          </div>

          {{-- Away --}}
          <div class="flex justify-between items-center gap-1">
            <div class="flex gap-1">
              <img src="{{ $j->awayTeam->logo }}" alt="{{ $j->awayTeam->nombre }}" class="w-6 h-6">
              <span class="font-medium text-sm">{{ $j->awayTeam->nombre }}</span>
            </div>
            <div>
              {{ $j->away_score ?? "???" }}
            </div>
          </div>
        </div>

        <div class="flex items-center justify-center">
          @if ($j->status == "FT")
            <a
              wire:navigate
              href="{{ route('evento.juego-index', ['evento' => $evento, 'juego' => $j]) }}"
              >
              <x-icon name="fas.bullseye" class="w-8 h-8 text-accent" />
            </a>
          @endif
        </div>

        <div class="flex items-center justify-center">
          @if($j->youtube)
            <a href="{{ $j->youtube }}" target="_blank">
              <x-icon name="fab.youtube" class="w-10 h-10 text-error" />
            </a>
          @endif
        </div>

      </div>
    @endforeach

  </div>
</div>
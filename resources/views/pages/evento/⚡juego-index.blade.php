<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Juego;

new class extends Component
{
  public Evento $evento;
  public Juego $juego;
  public $pronosticos;

  public function mount(Evento $evento, Juego $juego)
  {
    $this->evento = $evento;
    $this->juego  = $juego;

    $this->pronosticos = $juego->pronosticos()
      ->with('participacion')
      ->where('juego_id', $juego->id)
      ->whereHas('participacion', function($query) {
        $query->where('evento_id', $this->evento->id);
      })
      ->get();
    info($this->pronosticos);
  }
};
?>

<div>
  <div class="grid grid-cols-3 py-2 px-4 mt-2 rounded-xl overflow-hidden shadow-md bg-base-100 mb-4">
    <div class="col-span-3 flex items-center justify-between mb-4">
      <div></div>
      <div class="text-base-content/50">{{ $juego->valido_hasta }}</div>
      <div>
        <x-badge
          value="{{ $juego->status }}"
          class="badge-sm badge-info"
          />
      </div>
    </div>

    <div class="items-center flex flex-col justify-center">
      <img src="{{ $juego->homeTeam->logo }}" class="w-12 h-12">
      <p>{{ $juego->homeTeam->nombre }}</p>
    </div>
    <div>
      <p class="text-4xl font-bold flex items-center justify-center h-full">
        {{ $juego->home_score }} - {{ $juego->away_score }}
      </p>
    </div>
    <div class="items-center flex flex-col justify-center">
      <img src="{{ $juego->awayTeam->logo }}" class="w-12 h-12">
      <p>{{ $juego->awayTeam->nombre }}</p>
    </div>
  </div>

  <div class="rounded-xl overflow-hidden shadow-md">

    <div class="grid grid-cols-5 gap-2 py-1 px-2 bg-base-100 font-bold text-sm">
      <div class="col-span-3">Participación</div>
      <div class="flex items-center justify-center">Pronóstico</div>
      <div class="flex items-center justify-center">Resultado</div>
    </div>

    @foreach ($pronosticos as $p)

      @php
        if ($p->res == 1) {
          if ($p->dif == 1) {
            $icon = 'fas.thumbs-up';
            $iconClass = 'text-success';
            $class = "bg-success/30 text-success";
          } else {
            $icon = 'fas.thumbs-up';
            $iconClass = 'text-warning';
            $class = "bg-warning/30 text-warning";
          }
        } else {
          $icon = 'fas.thumbs-down';
          $iconClass = 'text-error';
          $class = "bg-error/30 text-error";
        }
      @endphp

      <div class="grid grid-cols-5 gap-2 py-1 px-2 {{ $class }}">
        <div class="col-span-3">
          <a wire:navigate href="{{ route('evento.resultados', ['evento' => $evento, 'participacion' => $p->participacion]) }}">
            {{ $p->participacion->nombre }}
          </a>
        </div>

        <div class="flex items-center justify-center">{{ $p->diferencia }}</div>

        <div class="flex items-center justify-center">
          <x-icon name="{{ $icon }}" class="w-4 h-4 {{ $iconClass }}" />
        </div>
      </div>

    @endforeach
  </div>
</div>
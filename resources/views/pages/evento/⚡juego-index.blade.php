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

<x-card class="bg-base-100">
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

      <div>{{ $p->diferencia }}</div>

      <div class="flex items-center justify-center">
        <x-icon name="{{ $icon }}" class="w-4 h-4 {{ $iconClass }}" />
      </div>
    </div>
  @endforeach
</x-card>
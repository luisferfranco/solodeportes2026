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
  <div class="grid grid-cols-5 gap-2">
    @foreach ($pronosticos as $p)
      <div class="col-span-3">
        {{ $p->participacion->nombre }}
      </div>

      <div>{{ $p->diferencia }}</div>

      <div>
        @if ($p->res == 1)
          @if ($p->dif == 1)
            <x-icon name="fas.thumbs-up" class="w-6 h-6 text-success" />
          @else
            <x-icon name="fas.thumbs-up" class="w-6 h-6 text-warning" />
          @endif
        @else
          <x-icon name="fas.thumbs-down" class="w-6 h-6 text-error" />
        @endif
      </div>
    @endforeach
  </div>
</x-card>
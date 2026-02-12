<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }
};
?>

<div>
  <x-title :title="$evento->nombre" />

  <div class="flex gap-4 items-start">
    <div>
      <img src="{{ $evento->imagenUrl }}" class="w-64 h-48 object-cover rounded" />
      @if ($evento->imagen === null)
        <p class="text-xs text-base-content/50">Imagen por default</p>
      @endif
    </div>

    <div class="w-full">
      <div class="bg-base-200 px-6 py-2 rounded w-full shadow-md">

        <div class="mb-2">
          <x-icon name="{{ $evento->deporte->icono }}" class="h-3 w-3" />
          <span>{{ $evento->deporte->nombre }} (Temporada {{ $evento->temporada->temporada }})</span>
        </div>
        <div class="flex items-baseline mb-2">
          <p><x-badge :value="$evento->tipoJuego->nombre" class="badge-info badge-sm" /></p>
          <p><x-badge :value="$evento->estado->label()" class="badge-{{ $evento->estado->color() }} badge-sm ml-2" /></p>
        </div>

        <h3 class="font-bold text-xl">Descripción</h3>
        <div>
          {!! Str::markdown($evento->descripcion) !!}
        </div>
        @if ($evento->reglas)
          <h3 class="font-semibold mt-4 text-xl">Reglas</h3>
          <div class="prose prose-sm max-w-none">
            {!! Str::markdown($evento->reglas) !!}
          </div>
        @else
          <p class="text-sm text-base-content/50 mt-4">No hay reglas específicas para este evento.</p>
        @endif
      </div>

      <div class="mt-4 bg-base-200 px-6 py-2 rounded w-full shadow-md grid grid-cols-2">
        <div>
          <p class="text-base-content/50">Precio</p>
          <p class="font-bold">{{ Number::format($evento->precio,2) }}</p>
        </div>
        <div>
          <p class="text-base-content/50">Puntos de Acierto</p>
          <p class="font-bold">{{ Number::format($evento->acierto ?? 0) }}</p>
        </div>
        <div></div>
        <div>
          <p class="text-base-content/50">Puntos de Diferencia</p>
          <p class="font-bold">{{ Number::format($evento->diferencia ?? 0) }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $boletos;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->boletos = $evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }

};
?>

<div>
  <p class="text-xs text-base-content/50 mt-2">Tienes {{ $boletos->count() }} boleto{{ $boletos->count() > 1 ? 's' : '' }}</p>
  @if ($boletos->count() > 0)
    <div class="mt-auto pb-2">
      @foreach ($boletos as $boleto)
          <x-button
            link="{{ route(strtolower($boleto->evento->temporada->deporte_id) . '.' . $boleto->evento->tipojuego_id . '.pronosticos', [$boleto->evento, 'p' => $boleto->id]) }}"
            class="btn-sm btn-secondary w-full mb-2"
            >
            <div class="flex justify-between gap-2 items-center w-full">
              <div class="flex gap-1 items-center">
                <x-icon name="fas.ticket" class="text-secondary-content" />
                <p class="text-sm text-secondary-content">#{{ sprintf("%05d",$boleto->id) }} {{ $boleto->nombre }}</p>
              </div>

              <x-icon name="fas.play" class="w-3 h-3" />
            </div>
          </x-button>
      @endforeach
    </div>
  @endif
</div>

<?php

use App\Models\Juego;
use App\Models\Participacion;
use App\Models\Pronostico;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Juego $juego;
  public $valido;
  public $prono;
  public Participacion $participacion;
  public float $localPorcentaje = 0;
  public float $visitantePorcentaje = 0;
  public int $totalPronosticos = 0;

  public function mount(Juego $juego, Participacion $participacion) {
    $this->participacion = $participacion;
    $this->juego = $juego;
    $this->prono = Pronostico::where('juego_id', $juego->id)
      ->where('participacion_id', $participacion->id)
      ->value('diferencia');
    $this->valido = Gate::allows('pronosticar', $this->juego);

    $this->actualizaPorcentajes();
  }

  public function actualizaPorcentajes(): void
  {
    $pronosticos = Pronostico::where('juego_id', $this->juego->id)->get();
    $this->totalPronosticos = $pronosticos->count();

    if ($this->totalPronosticos === 0) {
      $this->localPorcentaje = 0;
      $this->visitantePorcentaje = 0;

      return;
    }

    $local = $pronosticos->filter(fn ($pronostico) => (int) $pronostico->diferencia > 0)->count();
    $visitante = $pronosticos->filter(fn ($pronostico) => (int) $pronostico->diferencia < 0)->count();

    $this->localPorcentaje = round(($local / $this->totalPronosticos) * 100, 1);
    $this->visitantePorcentaje = round(($visitante / $this->totalPronosticos) * 100, 1);
  }

  public function pronostica($prono) {
    if (!$this->valido) {
      return;
    }
    $this->prono = $prono;
    Pronostico::updateOrCreate([
      'juego_id' => $this->juego->id,
      'participacion_id' => $this->participacion->id,
    ], [
      'diferencia' => $prono,
    ]);

    $this->actualizaPorcentajes();
  }
};
?>

<div class="{{ $valido ? 'bg-base-100' : 'bg-error/30' }} rounded-xl my-3 overflow-hidden border {{ $valido ? 'border-success/50' : 'border-error/50' }}">
  <div class="flex items-center justify-between px-2 py-1 {{ $valido ? 'bg-success/50' : 'bg-error/50' }} text-base-content">
    <div>Valido hasta el <span class="font-bold">{{ $juego->valido_hasta }}</span> ({{ $juego->valido_hasta->diffForHumans() }})</div>
    <div>Juego #{{ $juego->id }}</div>
  </div>

  <div class="flex items-center justify-center gap-4 text-xs">
    <div class="flex flex-col gap-1 w-1/2">
      <div class="flex items-center gap-1 justify-end">
        <img src="{{ $juego->awayTeam->logo }}" class="h-8 w-8 md:h-10 md:w-10">
        <div class="text-xs md:text-base">{{ $juego->awayTeam->nombre }}</div>
      </div>
    </div>
    <div class="flex flex-col gap-1 w-1/2">
      <div class="flex justify-start items-center gap-1">
        <p class="text-center text-xs md:text-base">{{ $juego->homeTeam->nombre }}</p>
        <p class="text-center text-xs md:text-base"><img src="{{ $juego->homeTeam->logo }}" class="h-8 w-8 md:h-10 md:w-10"></p>
      </div>
    </div>
  </div>

  <div class="flex items-center justify-center gap-4">
    <div class="flex items-center justify-end gap-1">
      @for ($i = -4; $i < 0; $i++)
        <x-button
          label=" {{ abs($i) }} "
          class="h-9 w-9 rounded {{ $prono === $i ? 'bg-red-800 text-white' : 'bg-gray-300 dark:bg-gray-700 hover:bg-gray-500' }}"
          wire:click='pronostica({{ $i }})'
          spinner
          />
      @endfor
    </div>
    <div class="flex items-center justify-start gap-1">
      @for ($i = 1; $i <= 4; $i++)
        <x-button
          label=" {{ $i }} "
          class="h-9 w-9 rounded {{ $prono === $i ? 'bg-red-800 text-white' : 'bg-gray-300 dark:bg-gray-700 hover:bg-gray-500' }}"
          wire:click='pronostica({{ $i }})'
          spinner
          />
      @endfor
    </div>
  </div>

  <div class="flex items-center justify-center gap-4 pb-2">
    <div class="flex justify-end w-1/2 {{ $visitantePorcentaje > $localPorcentaje ? 'font-bold text-red-500' : '' }}">
      @if ($totalPronosticos > 0)
        {{ number_format($visitantePorcentaje, 1) }}%
      @else
        Sin pronósticos
      @endif
    </div>
    <div class="flex justify-start w-1/2 {{ $localPorcentaje > $visitantePorcentaje ? 'font-bold text-red-500' : '' }}">
      @if ($totalPronosticos > 0)
        {{ number_format($localPorcentaje, 1) }}%
      @else
        Sin pronósticos
      @endif
    </div>
  </div>

</div>
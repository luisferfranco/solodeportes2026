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

  public function mount(Juego $juego, Participacion $participacion) {

    $this->juego  = $juego;
    $this->prono  = Pronostico::where('juego_id', $juego->id)
      ->where('participacion_id', $participacion->id)
      ->value('diferencia');
    $this->valido = Gate::allows('pronosticar', $this->juego);
  }

  public function pronostica($prono) {
    if (!$this->valido) {
      return;
    }
    $this->prono = $prono;
    $p = Pronostico::updateOrCreate([
      'juego_id'          => $this->juego->id,
      'participacion_id'  => $this->participacion->id
    ], [
      'diferencia'        => $prono
    ]);
  }
};
?>

<div class="{{ $valido ? 'bg-base-100' : 'bg-error/30' }} rounded-xl my-3 overflow-hidden border {{ $valido ? 'border-success/50' : 'border-error/50' }}">
  <div class="flex items-center justify-between px-2 py-1 {{ $valido ? 'bg-success/50' : 'bg-error/50' }} text-base-content">
    <div>Valido hasta el <span class="font-bold">{{ $juego->valido_hasta }}</span> ({{ $juego->valido_hasta->diffForHumans() }})</div>
    <div>Juego #{{ $juego->id }}</div>
  </div>
  <div class="px-2 py-1 flex items-center justify-between gap-2 my-4">
    <div class="w-1/4">
      <div class="flex justify-center">
        <p class="text-center"><img src="{{ $juego->homeTeam->logo }}" class="lg:h-18 lg:w-18 h-8 w-8"></p>
      </div>
      <p class="text-center text-xs md:text-base">{{ $juego->homeTeam->nombre }}</p>
    </div>
    <div class="flex items-center justify-center grow">
      @for ($i = -2; $i <= 2; $i++)
        <x-button
          label=" {{ abs($i) }} "
          class="h-8 w-8 lg:h-12 lg:w-12 rounded {{ $prono === $i ? 'bg-red-800 text-white' : 'bg-gray-300 dark:bg-gray-700 hover:bg-gray-500' }}"
          wire:click='pronostica({{ $i }})'
          spinner
          />
      @endfor
    </div>
    <div class="w-1/4">
      <div class="flex justify-center">
        <p class="text-center"><img src="{{ $juego->awayTeam->logo }}" class="lg:h-18 lg:w-18 h-8 w-8"></p>
      </div>
      <p class="text-center text-xs md:text-base">{{ $juego->awayTeam->nombre }}</p>
    </div>
  </div>
</div>
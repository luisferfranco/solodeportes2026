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

    if ($juego->id == 2391746) {
      info($participacion);
    }

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
    Pronostico::updateOrCreate([
      'juego_id'          => $this->juego->id,
      'participacion_id'  => $this->participacion->id
    ], [
      'diferencia'        => $prono
    ]);
  }
};
?>

<div class="bg-base-100 rounded-xl my-3 overflow-hidden border {{ $valido ? 'border-success/50' : 'border-error/50' }}">
  <div class="flex items-center justify-between px-2 py-1 {{ $valido ? 'bg-success/50' : 'bg-error/50' }} text-base-content">
    <div>Valido hasta el <span class="font-bold">{{ $juego->valido_hasta }}</span> ({{ $juego->valido_hasta->diffForHumans() }})</div>
    <div>Juego #{{ $juego->id }}</div>
  </div>
  <div class="px-2 py-1 flex items-center justify-between gap-2 my-4">
    <div class="grow">
      <div class="flex justify-center">
        <p class="text-center"><img src="{{ $juego->homeTeam->logo }}" class="h-18 w-18"></p>
      </div>
      <p class="text-center">{{ $juego->homeTeam->nombre }}</p>
    </div>
    <div class="flex items-center justify-center gap-2 shrink-0">
      @for ($i = -2; $i <= 2; $i++)
        <x-button
          label=" {{ abs($i) }} "
          class="h-10 w-10 rounded {{ $prono === $i ? 'btn-secondary' : 'btn-neutral' }} hover:btn-accent btn-lg"
          wire:click='pronostica({{ $i }})'
          spinner
          />
      @endfor
    </div>
    <div class="grow">
      <div class="flex justify-center">
        <p class="text-center"><img src="{{ $juego->awayTeam->logo }}" class="h-18 w-18"></p>
      </div>
      <p class="text-center">{{ $juego->awayTeam->nombre }}</p>
    </div>
  </div>
</div>
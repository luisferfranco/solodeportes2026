<?php

use App\Models\Juego;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
  public Juego $juego;
  public $valido;
  public $prono;

  public function mount(Juego $juego) {
    $this->juego  = $juego;
    $prono = auth()->user()
      ->pronosticos()
      ->where('juego_id', $juego->id)
      ->first();
    $this->valido = Gate::allows('pronosticar', $this->juego);
  }

  public function prono($prono) {
    info($prono);
  }
};
?>

<div class="bg-base-100 rounded-xl my-3 overflow-hidden border border-gray-300 dark:border-gray-700">
  <div class="flex items-center justify-between px-2 py-1 {{ $valido ? 'bg-success text-success-content' : 'bg-error text-error-content' }}">
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
          class="h-10 w-10 rounded btn-neutral hover:btn-accent btn-lg"
          wire:click='prono({{ $i }})'
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
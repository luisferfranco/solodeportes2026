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
  public $pos, $neg, $emp, $tot;

  public function mount(Juego $juego, Participacion $participacion) {

    $this->calculaPronosticos();
    $this->participacion = $participacion;

    $this->juego  = $juego;
    $this->prono  = Pronostico::where('juego_id', $juego->id)
      ->where('participacion_id', $participacion->id)
      ->first()?->diferencia;

    // info('Inicial Pronostico para juego #' . $juego->id . ' y participacion #' . $participacion->id . ': ' . $this->prono);

    $this->valido = Gate::allows('pronosticar', $this->juego);
  }

  public function calculaPronosticos() {
    // Cálculo de los pronósticos generales
    $pronos = $this->participacion
      ->evento
      ->participaciones()
      ->with(['pronosticos' => function ($query) {
        $query->where('juego_id', $this->juego->id);
      }])->get();

    $arr = $pronos->flatMap(function ($p) {
      return $p->pronosticos->pluck('diferencia')->toArray();
    })->toArray();

    $this->pos = count(array_filter($arr, fn($x) => $x > 0));
    $this->neg = count(array_filter($arr, fn($x) => $x < 0));
    $this->emp = count(array_filter($arr, fn($x) => $x == 0));
    $this->tot = $this->pos + $this->neg + $this->emp;

    // Calcular porcentajes con verificación de división por cero
    $this->pos = $this->tot > 0 ? round(($this->pos / $this->tot) * 100, 2) : 0;
    $this->neg = $this->tot > 0 ? round(($this->neg / $this->tot) * 100, 2) : 0;
    $this->emp = $this->tot > 0 ? round(($this->emp / $this->tot) * 100, 2) : 0;
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

    $this->calculaPronosticos();
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

    <div>
      @if ($prono === null)
        <div class="py-1 px-2 bg-warning text-warning-content rounded mb-2">
          <p class="font-bold text-sm tracking-widest">ATENCIÓN</p>
          <p class="text-xs text-warning-content/75">Este partido aún no se ha pronosticado</p>
        </div>
      @endif
      <div class="flex items-center justify-center grow gap-1 lg:gap-2">
        @for ($i = -2; $i <= 2; $i++)
          <x-button
            label=" {{ abs($i) }} "
            class="h-8 w-8 lg:h-14 lg:w-14 rounded {{ $prono === $i ? 'bg-red-800 text-white' : 'bg-gray-300 dark:bg-gray-700 hover:bg-gray-500' }}"
            wire:click='pronostica({{ $i }})'
            spinner
            />
        @endfor
      </div>

      @if (auth()->user()->id == 1)
        <div class="mt-2 px-2 py-1 bg-base-200">
          <p class="text-center text-xs font-bold tracking-widest mb-2">Tendencias</p>
          <div class="flex items-center justify-between gap-1 font-bold text-base-content/50">
            <div>{{ Number::format($neg, precision: 2) }}%</div>
            <div>{{ Number::format($emp, precision: 2) }}%</div>
            <div>{{ Number::format($pos, precision: 2) }}%</div>
          </div>
        </div>
      @endif
    </div>

    <div class="w-1/4">
      <div class="flex justify-center">
        <p class="text-center"><img src="{{ $juego->awayTeam->logo }}" class="lg:h-18 lg:w-18 h-8 w-8"></p>
      </div>
      <p class="text-center text-xs md:text-base">{{ $juego->awayTeam->nombre }}</p>
    </div>
  </div>
</div>
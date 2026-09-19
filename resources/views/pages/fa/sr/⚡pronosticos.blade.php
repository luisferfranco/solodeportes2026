<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Survivor;
use App\Models\Equipo;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $participaciones;
  public $partId = null;
  public $participacion = null;
  public int $ronda;
  public $juegos;

  // Estado de la participación, vivo, muerto, indefinido
  public $estadoPart;

  // Equipos seleccionados anteriormente
  public $usados = [];

  public function mount(Evento $evento, ?Participacion $participacion = null) {
    if (Gate::forUser(auth()->user())->denies('view', $evento)) {
      $this->redirectRoute('evento.show', ['evento' => $evento]);
      return;
    }

    $this->evento = $evento;
    $this->participaciones = $evento->participaciones()
      ->where('user_id', auth()->id())
      ->with('user')
      ->get();

    $this->partId = request()->query('p')
      ?? $participacion?->id
      ?? $this->participaciones->first()?->id;

    $this->participacion = $this->participaciones
      ->firstWhere('id', $this->partId);
    $this->ronda = (int) (request()->query('rd') ?? $evento->temporada->ronda);

    $this->usados = Survivor::query()
      ->where('participacion_id', $this->partId)
      ->where('ronda', '<', $this->ronda)
      ->with('equipo')
      ->get();

    $this->juegos = $this->evento
      ->temporada
      ->juegos()
      ->where('ronda', $this->ronda)
      ->with(['homeTeam', 'awayTeam'])
      ->orderBy('valido_hasta')
      ->get();

    $this->estadoPart = Survivor::where('participacion_id', $this->partId)
      ->where('ronda', $this->ronda)
      ->first()
      ->acierto;
  }
};
?>

<div>
  <section>
    <x-title title="{{ $evento->nombre }}" subtitle="Pronósticos" />

    <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="3" />

    @if ($participaciones->count() > 1)
    <livewire:selector-participacion :evento="$evento" :key="'selector-participacion-' . $evento->id" />
    @endif

    <livewire:selector-rondas :model="$evento" :ronda="$ronda" :key="'selector-ronda-' . $evento->id" />
  </section>

  {{-- Equipos seleccionados --}}
  <section class="mt-2 max-w-3xl mx-auto bg-base-300 py-1 px-2 rounded-lg">
    <x-label value="Equipos seleccionados anteriormente" />
    <div class="flex gap-1 mt-2">
      @foreach ($usados as $seleccion)
        <img src="{{ $seleccion->equipo->logo }}" alt="{{ $seleccion->equipo->nombre }}" class="h-12 w-12">
      @endforeach
    </div>
  </section>

  <div class="mt-2 max-w-3xl mx-auto">
    {{-- Estado de la participación --}}
    @if ($estadoPart === true)
      <x-alert
      class="alert-success"
      title="Sobreviviente"
      icon="fas.shield-heart"
      />
    @elseif ($estadoPart === false)
      <x-alert
      class="alert-error"
      title="Eliminado"
      icon="fas.skull-crossbones"
      />
    @else
      <x-alert
      class="alert-warning"
      title="Aguardando tu destino"
      icon="fas.ghost"
      />
    @endif
  </div>

  {{-- Selección de juego --}}
  <div class="grid grid-cols-2 gap-2 max-w-3xl mx-auto mt-2">
    @foreach ($juegos as $j)
      <livewire:survivor.survivor-button
        :equipo="$j->awayTeam"
        :participacion="$participacion"
        :usados="$usados"
        :ronda="$ronda"
        :key="'survivor-button-away-' . $j->id"
        />
      <livewire:survivor.survivor-button
        :equipo="$j->homeTeam"
        :participacion="$participacion"
        :usados="$usados"
        :ronda="$ronda"
        :key="'survivor-button-home-' . $j->id"
        />
    @endforeach
  </div>
</div>
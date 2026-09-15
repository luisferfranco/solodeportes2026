<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Survivor;

new class extends Component
{
  public Evento $evento;
  public $participaciones;
  public $partId = null;
  public $participacion = null;
  public int $ronda;
  public $juegos;

  // Estado de la participación, vivo, muerto, indefinido
  public $estadoPart;

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
  <x-title title="{{ $evento->nombre }}" subtitle="Pronósticos" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="3" />

  @if ($participaciones->count() > 1)
    <livewire:selector-participacion :evento="$evento" :key="'selector-participacion-' . $evento->id" />
  @endif

  <livewire:selector-rondas :model="$evento" :ronda="$ronda" :key="'selector-ronda-' . $evento->id" />

  <div class="mt-4 max-w-3xl mx-auto">
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
</div>
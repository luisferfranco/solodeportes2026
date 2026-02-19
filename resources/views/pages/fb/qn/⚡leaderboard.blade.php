<?php

use App\Models\Evento;
use App\Models\Leaderboard;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $rd;
  public $participaciones;
  public $resultados;
  public $headers;

  public function mount(Evento $evento)
  {
    $this->evento = $evento;
    $this->rd = request()->get('rd') ?? $this->evento->temporada->ronda;

    $aciertos     = $evento->acierto;
    $diferencias  = $evento->diferencia;

    $this->headers = [
      ['key' => 'participacion_id', 'label' => 'Participante'],
      ['key' => 'aciertos', 'label' => "Aciertos ($aciertos)", 'class' => 'text-right'],
      ['key' => 'diferencias', 'label' => "Diferencias ($diferencias)", 'class' => 'text-right'],
      ['key' => 'puntos', 'label' => 'Puntos', 'class' => 'text-right'],
    ];

    $this->resultados = Leaderboard::where('evento_id', $evento->id)
      ->where('ronda', $this->rd)
      ->orderByDesc('puntos')
      ->get();
  }

  #[On('ronda-seleccionada')]
  public function actualizarRonda($ronda) {
    $this->redirectRoute('fb.qn.leaderboard', ['evento' => $this->evento->id, 'rd' => $ronda]);
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Leaderboard" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" opc="2" />

  <livewire:selector-rondas :temporada="$evento->temporada" :key="'selector-ronda-' . $evento->id" />

  @php
    $topParticipaciones = $resultados->take(3);
    $medallas = [
      ['label' => 'Oro',    'bg' => 'bg-amber-200',   'ring' => 'ring-amber-300', 'text' => 'text-amber-400'],
      ['label' => 'Plata',  'bg' => 'bg-slate-200',   'ring' => 'ring-slate-300', 'text' => 'text-slate-400'],
      ['label' => 'Bronce', 'bg' => 'bg-orange-200',  'ring' => 'ring-orange-300', 'text' => 'text-orange-400'],
    ];
  @endphp

  @if ($topParticipaciones->isNotEmpty())
    <section class="mt-6">
      <h3 class="text-lg font-semibold tracking-wide">Medallero</h3>
      <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach ($topParticipaciones as $index => $row)
          @php $medalla = $medallas[$index] ?? $medallas[2]; @endphp
          <div class="rounded-2xl {{ $medalla['bg'] }} p-4 shadow-sm ring-1 {{ $medalla['ring'] }}">
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-3">
                <x-avatar
                  :image="$row->participacion->user->avatar"
                  class="h-12 w-12"
                />
                <div>
                  <p class="text-sm uppercase tracking-widest text-black/60">{{ $medalla['label'] }}</p>
                  <p class="text-lg font-semibold text-black">{{ $row->participacion->nombre }}</p>
                  <p class="text-xs text-black/60">De: {{ $row->participacion->user->name }}</p>
                </div>
              </div>
              <div>
                <x-icon name="fas.trophy" class="h-16 w-16 {{ $medalla['text'] }}" />
              </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-sm text-black/70">
              <span>Aciertos: {{ $row->aciertos }}</span>
              <span>Diferencias: {{ $row->diferencias }}</span>
              <span class="font-semibold text-black">{{ $row->puntos }} pts</span>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif


 <x-table
  :headers="$headers"
  :rows="$resultados"
  class="mt-6"
  :empty-message="'No hay resultados para esta ronda.'"
  >
  @scope('cell_participacion_id', $row)
    <div class="flex items-center gap-2">
      <x-avatar
        :image="$row->participacion->user->avatar"
        class="h-10 w-10"
      />
      <div>
        <p>{{ $row->participacion->nombre }}</p>
        <p class="text-base-content/50 text-xs">De: {{ $row->participacion->user->name }}</p>
      </div>
    </div>
  @endscope
 </x-table>
</div>
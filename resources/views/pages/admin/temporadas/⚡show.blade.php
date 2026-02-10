<?php

use App\Models\Juego;
use App\Models\Equipo;
use Livewire\Component;
use App\Models\Temporada;
use App\Services\APIService;

new class extends Component
{
  public Temporada $temporada;
  public $noEquipos;
  public $rondas=[];
  public $ronda=1;

  public $juegos;
  public $headers;

  public function mount(Temporada $temporada) {
    $this->temporada = $temporada;
    $this->ronda = $this->temporada->ronda;
    $this->noEquipos = Equipo::where('deporte_id', $this->temporada->deporte_id)
      ->count();

    $this->headers = [
      ['key' => 'id', 'label' => 'ID', 'class'=>"w-5"],
      ['key' => 'home_id', 'label' => 'ID Local'],
      ['key' => 'away_id', 'label' => 'ID Visitante'],
      ['key' => 'valido_hasta',    'label' => 'Valido Hasta', ],
    ];
    $this->getData();
  }

  public function cargarEquipos() {
    $apiService = new APIService();
    $apiService->cargarEquipos($this->temporada);

    $this->noEquipos = Equipo::where('deporte_id', $this->temporada->deporte_id)
      ->count();
  }

  public function cargarRondas() {
    $apiService = new APIService();
    $apiService->cargarRondas($this->temporada);
    $this->getData();
  }

  public function getData() {
    $this->juegos = Juego::where('temporada_id', $this->temporada->id)
      ->where('ronda', $this->ronda)
      ->with(['equipoLocal', 'equipoVisitante'])
      ->get();

    // Obtener la ronda máxima
    $maxRonda = Juego::where('temporada_id', $this->temporada->id)
      ->max('ronda');
    $this->rondas = [];
    for ($i=1; $i<=$maxRonda; $i++) {
      $this->rondas[] = [
        'id'    => $i,
        'name'  => "Ronda {$i}",
      ];
    }
  }

  public function updatedRonda() {
    $this->getData();
  }
};
?>

<div>
  <x-title title="{{ $temporada->nombre }} ({{ $temporada->temporada }})" />

  <div class="flex gap-1">
    <x-button
      label="Equipos"
      icon="fas.people-group"
      class="btn-primary btn-sm"
      wire:click='cargarEquipos'
      spinner="cargarEquipos"
      />
    <x-button
      label="Rondas"
      icon="fas.calendar-days"
      class="btn-primary btn-sm"
      wire:click='cargarRondas'
      spinner="cargarRondas"
      />
  </div>

  <x-select
    wire:model.live='ronda'
    label="Ronda"
    class="outline-none!"
    :options="$rondas"
    />

  <x-table
    :headers="$headers"
    :rows="$juegos"
    >
    @scope("cell_home_id", $row)
      <div class="flex gap-1 justify-end items-baseline">
        <p>{{ $row->equipoLocal->nombre }}</p>
        <img src="{{ $row->equipoLocal->logo }}" class="w-6 h-6" />
      </div>
    @endscope
    @scope("cell_away_id", $row)
      <div class="flex gap-1 justify-start items-baseline">
        <img src="{{ $row->equipoVisitante->logo }}" class="w-6 h-6" />
        <p>{{ $row->equipoVisitante->nombre }}</p>
      </div>
    @endscope
  </x-table>



</div>
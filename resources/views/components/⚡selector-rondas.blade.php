<?php

use App\Models\Temporada;
use App\Services\APIService;
use App\Services\FBService;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evento;

new class extends Component
{
  use Toast;

  public Model $model;
  public $ronda;
  public $options = [];

  public function mount(Model $model) {

    info("Selector Rondas mount: " . get_class($model));

    if ($model instanceof Temporada) {
      $jornada_inicial  = 1;
      $jornada_final    = $model->rondafinal;
      $this->ronda      = request()->query('rd') ?? $model->ronda;
      $postemporada     = true;
    } elseif ($model instanceof Evento) {
      $jornada_inicial  = $model->jornada_inicio;
      $jornada_final    = $model->jornada_fin;
      $this->ronda      = request()->query('rd') ?? $model->temporada->ronda;
      $postemporada     = $model->temporada->rondafinal == $model->jornada_fin;
    } else {
      throw new \Exception("Modelo no soportado");
    }

    for ($i=$jornada_inicial; $i<=$jornada_final; $i++) {
      $this->options[] = [
        'id'    => $i,
        'name'  => "Jornada ${i}",
      ];
    }

    // Solo hay postemporada si están las jornadas completas
    // Si es media quiniela (la mitad de las  jornadas), no se juega postemporada
    if ($postemporada) {
      $this->options[] = [
        'id'    => 100,
        'name'  => 'Playoffs'
      ];
    }
  }

  public function updatedRonda($ronda) {
    $this->dispatch('ronda-seleccionada', $this->ronda);
  }

  public function setRonda() {
    $this->temporada->ronda = $this->ronda;
    $this->temporada->save();
    $this->success('Ronda actualizada');
  }

  public function marcadores() {
    $APIService = new APIService();
    $APIService->cargarMarcadores($this->temporada, $this->ronda);
    $this->dispatch('marcadores-cargados', $this->ronda);
    $this->success('Marcadores cargados');
  }

  public function calificar() {
    $FBService = new FBService();
    $FBService->califica($this->temporada, $this->ronda);
    $this->dispatch('marcadores-cargados', $this->ronda);
    $this->success('Ronda calificada');
  }
};
?>

<div>
  <div class="w-full md:w-auto">
    <x-select
      wire:model.live='ronda'
      label="Selecciona la ronda"
      class="w-full outline-none! text-xl select-xl mb-4"
      :options="$options"
      placeholder="Selecciona la ronda"
      option-label="name"
      option-value="id"
      />
  </div>
  @if (auth()->user()->isAdmin)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 w-full md:w-auto">
      <x-button
        icon="fas.play"
        class="btn-primary btn-xl"
        label="Set Ronda"
        wire:click='setRonda'
        spinner
        />
      <x-button
        icon="far.circle-dot"
        class="btn-secondary btn-xl"
        label="Marcadores"
        wire:click='marcadores'
        spinner
        />
      <x-button
        icon="far.circle-check"
        class="btn-secondary btn-xl"
        label="Calificar"
        wire:click='calificar'
        spinner
        />
    </div>
  @endif
</div>
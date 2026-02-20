<?php

use App\Models\Temporada;
use App\Services\APIService;
use App\Services\FBService;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Temporada $temporada;
  public $ronda;
  public $options = [];

  public function mount(Temporada $temporada) {
    $this->temporada = $temporada;
    $this->ronda  = request()->query('rd') ?? $temporada->ronda;

    for ($i=1; $i<$temporada->rondafinal + 1; $i++) {
      $this->options[] = [
        'id' => $i,
        'name' => $i !== $temporada->rondafinal ? 'Ronda ' . $i : 'Eliminatoria Directa'
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

<div class="flex flex-col md:flex-row items-end w-full gap-2">
  <div class="grow w-full md:w-auto">
    <x-select
      wire:model.live='ronda'
      label="Selecciona la ronda"
      class="w-full outline-none! text-xl select-xl"
      :options="$options"
      placeholder="Selecciona la ronda"
      option-label="name"
      option-value="id"
      />
  </div>
  @if (auth()->user()->isAdmin)
    <div class="w-full md:w-auto flex justify-between gap-2">
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
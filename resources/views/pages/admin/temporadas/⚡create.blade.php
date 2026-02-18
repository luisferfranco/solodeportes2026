<?php

use Livewire\Component;
use App\Models\Temporada;

new class extends Component
{
  use Mary\Traits\Toast;

  public $deporte_id;
  public $nombre;
  public $sport_api_id;
  public $temporada_id;
  public $fecha_inicio;
  public $fecha_fin;
  public $ronda;
  public $rondafinal;

  public Temporada $temporada;
  public $deportes;

  public function mount(?Temporada $temporada) {
    $this->deportes = App\Models\Deporte::orderBy('nombre')->get();

    $this->temporada = $temporada ?? new Temporada();

    if ($temporada) {
      $this->deporte_id   = $temporada->deporte_id;
      $this->nombre       = $temporada->nombre;
      $this->sport_api_id = $temporada->sport_api_id;
      $this->temporada_id = $temporada->temporada;
      $this->fecha_inicio = $temporada->fecha_inicio;
      $this->fecha_fin    = $temporada->fecha_fin;
      $this->ronda        = $temporada->ronda;
      $this->rondafinal   = $temporada->rondafinal;
    }
  }

  public function save() {
    $this->validate([
      'deporte_id'    => 'required|exists:deportes,id',
      'nombre'        => 'required|string',
      'sport_api_id'  => 'nullable',
      'temporada_id'  => [
        'required',
        'string',
        \Illuminate\Validation\Rule::unique('temporadas', 'temporada')
          ->where('deporte_id', $this->deporte_id)
          ->ignore($this->temporada->id),
      ],
      'fecha_inicio'  => 'nullable|date',
      'fecha_fin'     => 'nullable|date',
      'ronda'         => 'required',
      'rondafinal'    => 'required',
    ]);

    $this->temporada->updateOrCreate(
      ['id' => $this->temporada->id],
      [
        'deporte_id'    => $this->deporte_id,
        'nombre'        => $this->nombre,
        'sport_api_id'  => $this->sport_api_id,
        'temporada'     => $this->temporada_id,
        'fecha_inicio'  => $this->fecha_inicio,
        'fecha_fin'     => $this->fecha_fin,
        'ronda'         => $this->ronda,
        'rondafinal'    => $this->rondafinal,
      ]
    );

    $this->success('Temporada guardada correctamente');
    return redirect()->route('admin.temporadas.index');
  }
};
?>

<div>
  <x-title :title="$temporada->exists ? 'Editar Temporada' : 'Crear una Temporada'" />

  <x-form wire:submit='save'>
    <x-select
      wire:model.live="deporte_id"
      :options="$deportes"
      option-label="nombre"
      option-value="id"
      label="Deporte ID"
      placeholder="Selecciona un deporte"
      class="outline-none!"
      inline
      required
      />
    <x-input
      wire:model="nombre"
      label="Nombre"
      class="outline-none!"
      placeholder="Nombre"
      inline
      required
      />
    <div class="grid grid-cols-2 gap-2">
      <x-input
        wire:model="sport_api_id"
        label="API ID"
        class="outline-none!"
        placeholder="API ID"
        inline
        />
      <x-input
        wire:model="temporada_id"
        label="Temporada"
        class="outline-none!"
        placeholder="2023/2024"
        inline
        required
        />
      <x-datetime
        wire:model='fecha_inicio'
        label="Fecha Inicio"
        class="outline-none!"
        placeholder="Fecha Inicio"
        inline
        />
      <x-datetime
        wire:model='fecha_fin'
        label="Fecha Fin"
        class="outline-none!"
        placeholder="Fecha Fin"
        inline
        />


      <x-input
        wire:model="ronda"
        label="Ronda"
        class="outline-none!"
        placeholder="Ronda"
        inline
        required
        />
      <x-input
        wire:model="rondafinal"
        label="Ronda Final"
        class="outline-none!"
        placeholder="Ronda Final"
        inline
        required
        />

    </div>
    <div class="flex gap-2">
      <x-button
        label="Guardar"
        type="submit"
        class="btn-primary mt-4"
        />
      <x-button
        label="Cancelar"
        class="btn-secondary mt-4"
        link="{{ route('admin.temporadas.index') }}"
        />
    </div>
  </x-form>
</div>
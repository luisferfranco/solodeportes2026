<?php

use App\Models\Deporte;
use Livewire\Component;
use App\Models\Temporada;

new class extends Component
{
  public $temporadas;
  public $open = false;
  public $headers;
  public $deportes;

  public $sport_api_id;
  public $deporte_id;
  public $nombre;
  public $temporada;
  public $ronda;
  public $rondafinal;


  public function mount() {
    $this->temporadas = Temporada::with('deporte')->get();
    $this->deportes = Deporte::orderBy('nombre')->get();

    $this->headers = [
      ['key' => 'id',           'label' => 'ID',      'class'=>"w-5"],
      ['key' => 'sport_api_id', 'label' => 'API ID',  'class'=>"w-10"],
      ['key' => 'deporte_id',   'label' => 'Nombre', ],
      ['key' => 'temporada',    'label' => 'Temporada'],
      ['key' => 'ronda',        'label' => 'Ronda'],
      ['key' => 'rondafinal',   'label' => 'Ronda Final'],
    ];
  }

  public function updatedDeporteId() {
    $deporte = Deporte::find($this->deporte_id);
    if ($deporte) {
      $this->sport_api_id = $deporte->apikey;
    }
  }

  public function save() {
    $data = $this->validate([
      'sport_api_id'  => 'required|string',
      'deporte_id'    => 'required|exists:deportes,id',
      'nombre'        => 'required|string',
      'temporada'     => 'required|string',
      'ronda'         => 'required|string',
      'rondafinal'    => 'required|string',
    ]);

    Temporada::create([
      'sport_api_id'  => $data['sport_api_id'],
      'deporte_id'    => $data['deporte_id'],
      'nombre'        => $data['nombre'],
      'temporada'     => $data['temporada'],
      'ronda'         => $data['ronda'],
      'rondafinal'    => $data['rondafinal'],
    ]);

    $this->temporadas = Temporada::with('deporte')->get();
    $this->open = false;
    $this->reset(['sport_api_id', 'deporte_id', 'nombre', 'temporada', 'ronda', 'rondafinal']);
  }

  public function editar($id) {
    $temporada = Temporada::find($id);
    if ($temporada) {
      $this->sport_api_id = $temporada->sport_api_id;
      $this->deporte_id = $temporada->deporte_id;
      $this->nombre = $temporada->nombre;
      $this->temporada = $temporada->temporada;
      $this->ronda = $temporada->ronda;
      $this->rondafinal = $temporada->rondafinal;
      $this->open = true;
    }
  }
};
?>

<div>
  <x-modal wire:model='open' class="backdrop-blur">
    <x-card>
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
          wire:model="sport_api_id"
          label="API ID"
          class="outline-none!"
          placeholder="API ID"
          inline
          />
        <x-input
          wire:model="nombre"
          label="Nombre"
          class="outline-none!"
          placeholder="Nombre"
          inline
          required
          />
        <x-input
          wire:model="temporada"
          label="Temporada"
          class="outline-none!"
          placeholder="2023/2024"
          inline
          required
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
        <x-button
          label="Guardar"
          type="submit"
          class="btn-primary mt-4"
          />
      </x-form>
    </x-card>
  </x-modal>

  <x-title title="Temporadas" />

  <x-button
    label="Temporada"
    class="btn-primary mb-4"
    icon="fas.circle-plus"
    wire:click="$set('open', true)"
    />

  <x-table
    :headers="$headers"
    :rows="$temporadas"
    >
    @scope('cell_deporte_id', $row)
      <x-icon name="{{ $row->deporte->icono }}" class="w-4 h-4" />
      {{ $row->nombre }}
    @endscope

    @scope('actions', $row)
      <x-button
        label="Editar"
        class="btn-secondary"
        icon="lucide.edit"
        wire:click="editar({{ $row->id }})"
        />
    @endscope
  </x-table>



</div>
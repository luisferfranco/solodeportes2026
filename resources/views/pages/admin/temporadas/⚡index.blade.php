<?php

use App\Models\Deporte;
use App\Models\Temporada;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
  public $temporadas;
  public $headers;

  public function mount() {
    $this->temporadas = Temporada::with('deporte')->get();
    $this->headers = [
      ['key' => 'id',           'label' => 'ID',      'class'=>"w-5"],
      ['key' => 'sport_api_id', 'label' => 'API ID',  'class'=>"w-10"],
      ['key' => 'deporte_id',   'label' => 'Nombre', ],
      ['key' => 'temporada',    'label' => 'Temporada'],
      ['key' => 'ronda',        'label' => 'Ronda'],
      ['key' => 'rondafinal',   'label' => 'Ronda Final'],
      ['key' => 'fecha_inicio', 'label' => 'Fecha Inicio'],
      ['key' => 'fecha_fin',    'label' => 'Fecha Fin'],
    ];
  }
};
?>

<div>
  <x-title title="Temporadas" />

  {{-- Crear temporada --}}
  <x-button
    label="Temporada"
    class="btn-primary mb-4"
    icon="fas.circle-plus"
    link="{{ route('admin.temporadas.create') }}"
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
      <div class="flex gap-2">
        <x-button
          label="Ver"
          class="btn-primary btn-sm"
          icon="fas.eye"
          link="{{ route('admin.temporadas.show', $row) }}"
          />
        <x-button
          label="Editar"
          class="btn-secondary btn-sm"
          icon="fas.edit"
          link="{{ route('admin.temporadas.edit', $row) }}"
          />
      </div>
    @endscope
  </x-table>



</div>
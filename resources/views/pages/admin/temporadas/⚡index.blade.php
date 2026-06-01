<?php

use App\Models\Deporte;
use App\Models\Temporada;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Services\APIService;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public $temporadas;
  public $headers;

  public $deporte_id, $temporada, $api_id, $nombre;

  public $modalCreate = false;

  public function mount() {
    $this->temporadas = Temporada::orderBy('id', 'desc')
      ->with('deporte')
      ->get();
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

  public function crearTemporada() {
    $this->validate([
      'deporte_id'  => 'required|exists:deportes,id',
      'temporada'   => 'required|string|max:255',
      'api_id'      => 'required|string|max:255',
      'nombre'      => 'required|string|max:255',
    ]);

    $temporada = Temporada::create([
      'deporte_id'  => $this->deporte_id,
      'temporada'   => $this->temporada,
      'sport_api_id' => $this->api_id,
      'nombre'      => $this->nombre,
    ]);

    $apiService = new APIService();

    $apiService->cargarEquipos($temporada);
    $apiService->cargarRondas($temporada);

    $temporada->rondafinal = $temporada->juegos()->max('ronda')+1;
    $temporada->fecha_inicio = $temporada->juegos()->min('valido_hasta');
    $temporada->fecha_fin = $temporada->juegos()->max('valido_hasta');
    $temporada->ronda = 1;
    $temporada->save();

    $this->success(
      title: 'Temporada creada',
      description: 'La temporada se ha creado correctamente y se han cargado los equipos y rondas desde la API.',
      icon: 'fas.check',
      timeout: 3000,
    );

    $this->temporadas = Temporada::orderBy('id', 'desc')
      ->with('deporte')
      ->get();

    $this->modalCreate = false;
  }

  public function cargarEquipos(Temporada $temporada) {
    $apiService = new APIService();
    $apiService->cargarEquipos($temporada);

    $this->success(
      title: 'Equipos cargados',
      description: 'Los equipos se han cargado correctamente desde la API.',
      icon: 'fas.check',
      timeout: 3000,
    );
  }

  public function cargarCalendario(Temporada $temporada) {
    $apiService = new APIService();
    $apiService->cargarRondas($temporada);

    $temporada->rondafinal = $temporada->juegos()->max('ronda')+1;
    $temporada->fecha_inicio = $temporada->juegos()->min('valido_hasta');
    $temporada->fecha_fin = $temporada->juegos()->max('valido_hasta');
    $temporada->save();

    $this->success(
      title: 'Calendario cargado',
      description: 'El calendario se ha cargado correctamente desde la API.',
      icon: 'fas.check',
      timeout: 3000,
    );
  }
};
?>

<div>
  {{-- Modal de creación de temporada --}}
  <x-modal
    wire:model='modalCreate'
    class="backdrop-blur"
    title="Crear una temporada"
    >
    <form wire:submit='crearTemporada' class="space-y-2">
      <x-label value="nombre" />
      <x-input
        class="outline-none!"
        wire:model="nombre"
        placeholder="Nombre de la temporada"
        required
        />
      <x-label value="deporte" />
      <x-select
        class="outline-none!"
        :options="Deporte::orderBy('nombre')->get()"
        option-label="nombre"
        option-value="id"
        wire:model="deporte_id"
        placeholder="Selecciona un deporte"
        />
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <x-label value="API ID" />
          <x-input
            class="outline-none!"
            wire:model="api_id"
            placeholder="ID API"
            required
            />
        </div>
        <div>
          <x-label value="Temporada" />
          <x-input
            class="outline-none!"
            wire:model="temporada"
            placeholder="Nombre de la temporada"
            required
            />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-button
          type="submit"
          class="btn-success"
          label="Crear"
          icon="fas.circle-plus"
          spinner="save"
          />
        <x-button
          type="button"
          class="btn-secondary btn-ghost"
          label="Cancelar"
          icon="fas.times"
          wire:click="$set('modalCreate', false)"
          />
      </div>
    </form>
  </x-modal>

  <x-title
    title="Admin::Temporadas"
    icon="lucide.calendar"
    />

  <x-card class="bg-base-100">
    {{-- Crear temporada --}}
    <x-button
      label="Temporada"
      class="btn-primary mb-4"
      icon="fas.circle-plus"
      wire:click="$set('modalCreate', true)"
      />

    <x-table
      :headers="$headers"
      :rows="$temporadas"
      :link="route('admin.temporadas.show', '[id]')"
      >
      @scope('cell_deporte_id', $row)
        <x-icon name="{{ $row->deporte->icono }}" class="w-4 h-4" />
        {{ $row->nombre }}
      @endscope

      @scope('actions', $row)
        <div class="flex gap-2">
          <x-button
            class="btn-primary btn-sm"
            icon="fas.eye"
            link="{{ route('admin.temporadas.show', $row) }}"
            tooltip-left="Ver"
            />
          <x-button
            class="btn-secondary btn-sm"
            icon="fas.edit"
            link="{{ route('admin.temporadas.edit', $row) }}"
            tooltip-left="Editar temporada"
            />
          <x-button
            class="btn-primary btn-sm"
            icon="fas.circle-info"
            wire:click="cargarEquipos({{ $row->id }})"
            tooltip-left="Cargar equipos"
            spinner
            />
          <x-button
            class="btn-primary btn-sm"
            icon="fas.calendar"
            wire:click="cargarCalendario({{ $row->id }})"
            tooltip-left="Cargar calendario"
            spinner
            />
        </div>
      @endscope
    </x-table>
  </x-card>
</div>
<?php

use App\Models\Evento;
use Mary\Traits\Toast;
use App\Models\Deporte;
use Livewire\Component;
use App\Models\Temporada;
use App\Models\TipoJuego;
use Livewire\WithFileUploads;

new class extends Component
{
  use WithFileUploads;
  use Toast;

  public $eventos, $evento;
  public $headers;

  public $show = false;
  public $tipoJuego, $tiposJuego;
  public $nombre;
  public $descripcion;
  public $jornadas, $jornada;
  public $deportes, $deporte;
  public $temporadas, $temporada;
  public $precio=2000;
  public $acierto=1;
  public $diferencia=0.5;
  public $file;
  public $estado="pendiente";

  public function mount() {
    $this->eventos = Evento::orderBy('created_at')
      ->get();
    $this->evento = new Evento();

    $this->headers = [
      ['key' => 'id', 'label' => 'Id', 'class' => 'w-8'],
      ['key' => 'tipojuego_id', 'label' => 'TJ', 'class' => 'w-8'],
      ['key' => 'nombre', 'label' => 'Nombre'],
      ['key' => 'estado', 'label' => 'Estado'],
      ['key' => 'temporada_id', 'label' => 'Temporada'],
    ];

    $this->tiposJuego = TipoJuego::orderBy('nombre')->get();
    $this->deportes = Deporte::orderBy('nombre')->get();
    $this->temporadas = collect();
  }

  public function updatedDeporte($deporteId) {
    info("Selected Deporte ID: $deporteId");
    $this->temporadas = Temporada::where('deporte_id', $deporteId)
      ->orderBy('temporada', 'desc')
      ->get();
  }

  public function save() {
    $this->validate([
      'nombre'      => 'required|string|max:255',
      'descripcion' => 'required|string',
      'deporte'     => 'required|exists:deportes,id',
      'temporada'   => 'required|exists:temporadas,id',
      'tipoJuego'   => 'required|exists:tipo_juegos,id',
      'precio'      => 'required|numeric|min:0',
      'acierto'     => 'required|integer|min:0',
      'diferencia'  => 'required|numeric|min:0',
      'file'        => 'nullable|image|max:2048', // Max 2MB
    ]);

    $this->evento->nombre = $this->nombre;
    $this->evento->descripcion = $this->descripcion;
    $this->evento->deporte_id = $this->deporte;
    $this->evento->temporada_id = $this->temporada;
    $this->evento->tipojuego_id = $this->tipoJuego;
    $this->evento->precio = $this->precio;
    $this->evento->acierto = $this->acierto;
    $this->evento->diferencia = $this->diferencia;
    $this->evento->estado = $this->estado;
    $this->evento->save();

    if ($this->file) {
      $path = $this->file->store('eventos', 'public');
      $this->evento->update(['imagen' => $path]);
    }

    $this->success('Evento creado exitosamente');

    $this->reset(['nombre', 'descripcion', 'deporte', 'temporada', 'tipoJuego', 'precio', 'acierto', 'diferencia', 'file']);

    $this->show = false;
    $this->mount(); // Refresh the eventos list
  }

  public function crearEvento() {
    $this->reset(['nombre', 'descripcion', 'deporte', 'temporada', 'tipoJuego', 'precio', 'acierto', 'diferencia', 'file']);
    $this->evento = new Evento();
    $this->show = true;
  }

  public function editarEvento(Evento $evento) {
    $this->evento       = $evento;
    $this->nombre       = $evento->nombre;
    $this->descripcion  = $evento->descripcion;
    $this->deporte      = $evento->deporte_id;
    $this->temporada    = $evento->temporada_id;
    $this->tipoJuego    = $evento->tipojuego_id;
    $this->precio       = $evento->precio;
    $this->acierto      = $evento->acierto;
    $this->diferencia   = $evento->diferencia;
    $this->estado       = $evento->estado;

    // Trigger the update of temporadas based on the selected deporte
    $this->updatedDeporte($evento->deporte_id);

    $this->show = true;
  }

  public function updatedEstado($estado) {
    $this->evento->estado = $estado;
    $this->evento->save();
    $this->mount(); // Refresh the eventos list
  }
};
?>

<div>
  <x-modal wire:model='show' class="backdrop-blur">
    <x-card title="{{ $evento->exists ? 'Editar Evento' : 'Crear Evento' }}" class="w-full max-w-lg">
      <x-form wire:submit='save'>
        <x-input
          wire:model='nombre'
          label="Nombre del Evento"
          placeholder="Nombre del Evento"
          class="outline-none!"
          required
          inline
          />
        <x-textarea
          wire:model='descripcion'
          label="Descripción del Evento"
          placeholder="Descripción del Evento"
          class="outline-none!"
          required
          inline
          />
        <div class="grid grid-cols-2 gap-2">
          <x-select
            wire:model.live='deporte'
            :options="$deportes"
            option-label="nombre"
            option-value="id"
            label="Deporte"
            placeholder="Selecciona un deporte"
            class="outline-none!"
            required
            inline
            />

          <x-select
            wire:model.live='temporada'
            :options="$temporadas"
            option-label="nombre"
            option-value="id"
            label="Temporada"
            placeholder="Temporada del Evento"
            class="outline-none!"
            :disabled="$temporadas->isEmpty()"
            required
            inline
            />

          <x-select
            wire:model.live='tipoJuego'
            :options="$tiposJuego"
            option-label="nombre"
            option-value="id"
            label="Tipo de Juego"
            placeholder="Selecciona un tipo de juego"
            class="outline-none!"
            required
            inline
            />

          <x-input
            wire:model='precio'
            label="Precio del Evento"
            placeholder="Precio del Evento"
            class="outline-none!"
            type="number"
            required
            inline
            />
          <x-input
            wire:model='acierto'
            label="Puntos por Acierto"
            placeholder="Puntos por Acierto"
            class="outline-none!"
            type="number"
            inline
            />
          <x-input
            wire:model='diferencia'
            label="Puntos por Diferencia"
            placeholder="Puntos por Diferencia"
            class="outline-none!"
            inline
            />

          @if ($evento->exists)
            <x-select
              wire:model.live='estado'
              :options="\App\Enums\EventoStatus::options()"
              label="Estado del Evento"
              placeholder="Selecciona un estado"
              class="outline-none!"
              required
              inline
              />
          @endif
        </div>

        <x-file
          wire:model='file'
          label="Imagen del Evento"
          placeholder="Imagen del Evento"
          class="outline-none!"
          inline
          />

        <div>
          @if ($evento->exists)
            <x-button
              label="Actualizar Evento"
              icon="fas.check-circle"
              class="btn-primary mt-4"
              type="submit"
              />
          @else
            <x-button
              label="Crear Evento"
              icon="fas.check-circle"
              class="btn-primary mt-4"
              type="submit"
              />
          @endif
        </div>

      </x-form>
    </x-card>
  </x-modal>

  <x-title title="Eventos" />

  <x-button
    label="Evento"
    icon="fas.plus-circle"
    class="btn-primary mb-4"
    wire:click="crearEvento()"
    />

  <x-table :headers="$headers" :rows="$eventos">
    @scope('cell_tipojuego_id', $e)
      <x-badge :value="$e->tipoJuego->nombre" class="badge-neutral badge-sm" />
    @endscope

    @scope('cell_nombre', $e)
      <a
        href="{{ route('admin.eventos.show', $e) }}"
        class="flex gap-1 items-baseline text-info hover:underline"
        >
        <x-icon name="{{ $e->deporte->icono }}" class="h-4 w-4 text-base-content/50" />
        <span>{{ $e->nombre }}</span>
      </a>
    @endscope

    @scope('cell_estado', $e)
      <livewire:estado-evento-select :evento="$e" />
      {{-- <x-badge :value="$e->estado->label()" class="badge-{{ $e->estado->color() }} badge-sm" /> --}}
    @endscope

    @scope('cell_temporada_id', $e)
      <span>{{ $e->temporada->temporada }}</span>
    @endscope

    @scope('actions', $e)
      <div class="flex gap-1">
        <x-button
          icon="lucide.edit"
          class="btn-ghost btn-xs"
          wire:click='editarEvento({{ $e }})'
          />
      </div>
    @endscope
  </x-table>

</div>
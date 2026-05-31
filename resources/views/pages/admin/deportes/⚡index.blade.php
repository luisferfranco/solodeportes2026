<?php

use Mary\Traits\Toast;
use App\Models\Deporte;
use Livewire\Component;

new class extends Component
{
  use Toast;

  public $deportes;
  public $headers;
  public $open = false;

  public $depoid, $apikey, $nombre, $descripcion, $icono;

  public function mount() {
    $this->deportes = Deporte::orderBy('nombre')->get();
    $this->headers = [
      ['key' => 'id', 'label' => 'ID', 'class'=>"w-5"],
      ['key' => 'nombre', 'label' => 'Nombre'],
    ];
  }

  public function save() {
    $data = $this->validate([
      'depoid'      => 'required|string|unique:deportes,id',
      'nombre'      => 'required|string',
      'descripcion' => 'nullable|string',
      'icono'       => 'nullable|string',
    ]);

    Deporte::create([
      'id'          => $data['depoid'],
      'apikey'      => $data['apikey'],
      'nombre'      => $data['nombre'],
      'descripcion' => $data['descripcion'] ?? '',
      'icono'       => $data['icono'] ?? '',
    ]);

    $this->success('Deporte creado correctamente');
    $this->deportes = Deporte::orderBy('nombre')->get();
    $this->open = false;
    $this->reset(['depoid', 'apikey', 'nombre', 'descripcion', 'icono']);
  }
};
?>

<div>
  <x-modal wire:model='open' class="backdrop-blur">
    <x-card>
      <x-form wire:submit='save'>
        <x-input class="outline-none!" label="ID" wire:model="depoid" />
        <x-input class="outline-none!" label="Nombre" wire:model="nombre" />
        <x-input class="outline-none!" label="Descripción" wire:model="descripcion" />
        <x-input class="outline-none!" label="Icono (fontawesome)" wire:model="icono" />

        <x-button label="Guardar" type="submit" class="btn-primary mt-4" />
      </x-form>
    </x-card>
  </x-modal>



  <x-title
    title="Admin::Deportes"
    subtitile="Gestiona los deportes disponibles en la plataforma"
    icon="lucide.medal"
    />

  <x-card class="bg-base-100">
    <x-button
      label="Agregar Deporte"
      class="btn-primary mb-4"
      icon="lucide.plus"
      wire:click="$set('open', true)"
      />

    <x-table
      :headers="$headers"
      :rows="$deportes"
      >
      @scope("cell_nombre", $r)
        <div class="flex gap-1 items-center">
          <x-icon :name="$r->icono" class="w-6 h-6 mr-1" />
          <div>
            <p>{{ $r->nombre }}</p>
            <p class="text-sm text-gray-500">{{ $r->descripcion }}</p>
          </div>
        </div>
      @endscope
    </x-table>
  </x-card>

</div>
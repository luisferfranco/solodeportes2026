<?php

use Livewire\Component;
use App\Models\Anuncio;

new
class extends Component
{
  public Anuncio $anuncio;
  public $show = false;

  public $anuncios;
  public $headers = [
    ['key' => 'id', 'label' => 'ID'],
    ['key' => 'titulo', 'label' => 'Titulo'],
    ['key' => 'desde', 'label' => 'Desde'],
    ['key' => 'hasta', 'label' => 'Hasta'],
  ];

  public function mount() {
    $this->anuncios = Anuncio::all();
    $this->anuncio = new Anuncio();
    // $this->anuncio = Anuncio::find(3);
  }

  public function showAnuncio($id) {
    $this->anuncio = Anuncio::find($id);
    $this->show = true;
  }

  public function delete($id) {
    $anuncio = Anuncio::find($id);
    if ($anuncio) {
      $anuncio->delete();
      $this->anuncios = Anuncio::all();
    }
  }
};
?>

<div>
  <x-modal
    wire:model="show"
    class="bg-base-300/50 backdrop-blur-md"
    box-class="max-w-5xl! bg-base-100"
    >
    <div>{!! Str::of($anuncio->cuerpo)->markdown() !!}</div>
  </x-modal>

  <x-card class="bg-base-100">
    <x-title title="Anuncios" subtitle="Listado de anuncios" />

    <div class="flex justify-end">
      <x-button
        link="{{ route('admin.anuncios.create') }}"
        class="btn-primary"
        icon="fas.plus-circle"
        >
        Crear Anuncio
      </x-button>
    </div>

    <x-table
      :headers="$headers"
      :rows="$anuncios"
      >

      @scope("cell_titulo", $row)
        <a
          wire:click="showAnuncio({{ $row->id }})"
          class="link link-hover"
          >
          <p class="font-bold">{{ $row->titulo }}</p>
        </a>
        <p class="text-xs text-base-content/50">{{ $row->created_at->diffForHumans() }}</p>
      @endscope

      @scope('actions', $row)
        <div class="flex gap-1">
          <x-button
            wire:click="showAnuncio({{ $row->id }})"
            icon="fas.eye"
            class="btn-secondary btn-square"
            />
          <x-button
            link="{{ route('admin.anuncios.edit', $row) }}"
            icon="fas.pencil"
            class="btn-primary btn-square"
            />
          <x-button
            wire:click="delete({{ $row->id }})"
            icon="fas.trash"
            class="btn-error btn-square"
            />
        </div>
      @endscope
    </x-table>
  </x-card>
</div>
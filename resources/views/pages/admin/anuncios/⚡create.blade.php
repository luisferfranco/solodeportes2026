<?php

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Layout;
use App\Models\Anuncio;

new
class extends Component
{
  use Toast;

  public Anuncio $anuncio;
  public $user;
  public $titulo = "", $cuerpo = "", $desde = "", $hasta = "";

  public function mount(?Anuncio $anuncio = null) {
    $this->user = auth()->user();
    $this->anuncio = $anuncio ?? new Anuncio();
    $this->titulo = $this->anuncio->titulo ?? "";
    $this->cuerpo = $this->anuncio->cuerpo ?? "";
    $this->desde = $this->anuncio->desde ?? "";
    $this->hasta = $this->anuncio->hasta ?? "";
  }

  public function save() {
    $this->validate([
      'titulo' => 'required|string|max:255',
      'cuerpo' => 'required|string',
    ]);

    $this->anuncio->titulo = $this->titulo;
    $this->anuncio->cuerpo = $this->cuerpo;
    $this->anuncio->desde = $this->desde === "" ? null : $this->desde;
    $this->anuncio->hasta = $this->hasta === "" ? null : $this->hasta;
    $this->anuncio->autor_id = $this->user->id;
    $this->anuncio->save();

    $this->success(
      title: 'Anuncio creado/editado',
      description: 'El anuncio ha sido creado/editado correctamente.',
      timeout: 5000,
      redirectTo: route('admin.anuncios.index'),
    );
  }
};
?>

<x-card class="bg-base-100">
  <x-title :title="$user->displayName" subtitle="Crear Anuncio" />

  <x-form wire:submit="save">
    <x-input
      wire:model="titulo"
      label="Titulo"
      />
    <x-markdown
      wire:model="cuerpo"
      label="Blog post"
      />

    <div class="grid grid-cols-2 gap-4">
      <x-datetime
        wire:model="desde"
        label="Desde"
        />
      <x-datetime
        wire:model="hasta"
        label="Hasta"
        />
    </div>

    <div class="flex items-center justify-end">
      <x-button link="{{ route('admin.anuncios.index') }}" class="btn-ghost">Cancelar</x-button>
      <x-button type="submit" class="btn-primary">Crear Anuncio</x-button>
    </div>
  </x-form>

</x-card>

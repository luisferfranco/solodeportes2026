<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $participaciones;
  public $headers;

  public function mount(Evento $evento) {
    $this->evento = $evento;

    $this->participaciones = $evento->participaciones()
      ->with('user')
      ->get();
    $this->headers = [
      ['key' => 'id',         'label' => 'ID', 'class'=>"w-5"],
      ['key' => 'nombre',     'label' => 'Nombre'],
      ['key' => 'user.name',  'label' => 'Usuario'],
      ['key' => 'created_at', 'label' => 'Fecha de Participación'],
    ];
  }
};
?>

<div>
  <x-title :title="$evento->nombre" />

  <livewire:evento-info :evento="$evento" />

  <x-card class="bg-base-100 mt-4">
    <x-table
      :headers="$headers"
      :rows="$participaciones"
      />

  </x-card>
</div>
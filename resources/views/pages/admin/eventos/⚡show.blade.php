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
      ['key' => 'id', 'value' => 'ID', 'class'=>"w-5"],
      ['key' => 'nombre', 'value' => 'Nombre'],
      ['key' => 'user.name', 'value' => 'Usuario'],
      ['key' => 'created_at', 'value' => 'Fecha de Participación'],
    ];
  }
};
?>

<div>
  <x-title :title="$evento->nombre" />

  <livewire:evento-info :evento="$evento" />

  <x-card class="bg-base-100">
    <x-table
      :headers="$headers"
      :rows="$participaciones"
      :link=""
      />
  </x-card>
</div>
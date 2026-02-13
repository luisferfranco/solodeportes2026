<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }

  public function comprar() {
    $this->dispatch('open-modal-comprar');
  }
};
?>

<div>
  <livewire:modal-comprar-evento :evento="$evento" />

  <x-title title="{{ $evento->nombre }}" />

  <livewire:nav-evento :evento="$evento" opc="1" />

  <livewire:evento-info :evento="$evento" />

  <div class="my-4">
    <x-button
      label="Comprar"
      icon="fas.cart-shopping"
      class="btn-primary"
      wire:click='comprar'
      spinner="comprar"
      />
  </div>

  <livewire:boletos-usuario :evento="$evento" />
</div>
<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" />

  <livewire:nav-evento :evento="$evento" opc="1" />

  <livewire:evento-info :evento="$evento" />

  <div class="mt-4">
    <x-button
      label="Comprar"
      icon="fas.cart-shopping"
      class="btn-primary"
      />
  </div>
</div>
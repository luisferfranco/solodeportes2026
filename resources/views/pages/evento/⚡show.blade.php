<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;

  public function mount(Evento $evento)
  {
    $this->evento = $evento;
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Detalle del evento" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" />

  <livewire:evento-info :evento="$evento" />
</div>
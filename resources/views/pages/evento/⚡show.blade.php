<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $administrador = false;

  public function mount(Evento $evento)
  {
    $this->evento = $evento;
    $this->administrador = $evento->administradores()->where('user_id', auth()->id())->exists();
  }
};
?>

<div>
  <x-title title="{{ $evento->nombre }}" subtitle="Detalle del evento" />

  <livewire:nav-evento :evento="$evento" :key="'nav-evento-' . $evento->id" />

  <div class="space-y-4">
    <livewire:evento-info :evento="$evento" />

    @if ($administrador)
      <livewire:evento.administradores :evento="$evento" />
      <livewire:evento.invitados :evento="$evento" />
    @endif
  </div>
</div>
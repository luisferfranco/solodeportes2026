<?php

use App\Models\Evento;
use Livewire\Component;

new class extends Component
{
  public Evento $evento;
  public $boletos;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->boletos = $evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }

};
?>

<div>
  <p class="text-xs text-base-content/50 mb-1">Tienes {{ $boletos->count() }} boleto{{ $boletos->count() > 1 ? 's' : '' }}</p>
  @if ($boletos->count() > 0)
    <div class="mt-auto py-2">
      @foreach ($boletos as $boleto)
        <div class="p-2 mb-2 bg-secondary/20 rounded-lg flex items-center gap-2">
          <x-icon name="fas.ticket" class="text-secondary" />
          <p class="text-sm text-secondary">#{{ sprintf("%05d",$boleto->id) }} {{ $boleto->nombre }}</p>
        </div>
      @endforeach
    </div>
  @endif
</div>

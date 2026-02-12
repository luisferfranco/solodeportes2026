<?php

use Livewire\Component;
use App\Models\Transaccion;

new class extends Component
{
  public $transaccion;

  public function mount(Transaccion $transaccion) {
    $this->transaccion = $transaccion;
  }
};
?>

<div>
  <x-title title="Detalle de Transacción" />

  <div class="max-w-4xl mx-auto">

    <x-button
      label="Volver al Banco"
      icon="fas.arrow-left"
      class="btn-ghost btn-primary mb-4"
      link="{{ route('banco') }}"
      />

    <div class="flex flex-col-reverse md:flex-row gap-4 items-start">

      @if ($transaccion->comprobante)
        <div class="w-full md:w-96 rounded shadow-md overflow-hidden">
          <img
            src="{{ asset('/storage/' . $transaccion->comprobante) }}"
            alt="Comprobante"
            class="w-full h-auto rounded shadow-md"
            >
        </div>
      @endif

      <x-card class="w-full md:w-96 md:grow">
        <div class="grid grid-cols-2 gap-2">
          <div>ID</div>
          <div>{{ sprintf('%06d', $transaccion->id) }}</div>

          <div>Fecha y Hora</div>
          <div>{{ $transaccion->created_at }}</div>

          <div><x-badge value="{{ $transaccion->tipo->label() }}" class="badge-{{ $transaccion->tipo->color() }}" /></div>
          <div><x-badge value="{{ $transaccion->estado->label() }}" class="badge-{{ $transaccion->estado->color() }}" /></div>

          <div class="text-xl">Monto</div>
          <div class="text-xl font-bold {{ $transaccion->monto > 0 ? 'text-success' : 'text-error' }}">$ {{ Number::format($transaccion->monto, 2) }}</div>

          @if ($transaccion->notas)
            <div class="col-span-2 border-t border-base-content/50 mt-4 pt-4">Notas:</div>
            <div class="col-span-2 text-base-content/50 text-sm">{{ $transaccion->notas }}</div>
          @endif
        </div>
      </x-card>
    </div>
  </div>



</div>
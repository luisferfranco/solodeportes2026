<?php

use App\Enums\TipoTransaccion;
use App\Models\Transaccion;
use Livewire\Component;

new class extends Component
{
  public $transacciones;
  public $headers;

  public function mount()
  {
    $this->transacciones = Transaccion::query()
      ->where('estado', 'pendiente')
      ->whereIn('tipo', ['deposito', 'retiro'])
      ->orderBy('updated_at', 'desc')
      ->get();
    $this->headers = [
      ['key' => 'usuario', 'label' => 'Usuario'],
      ['key' => 'tipo', 'label' => 'Tipo'],
      ['key' => 'estado', 'label' => 'Estado'],
      ['key' => 'monto', 'label' => 'Monto', 'class' => 'text-right'],
    ];
  }
};
?>

<div>
  <x-title>Depósitos y Retiros Pendientes</x-title>

  <div class="overflow-x-auto">

    <x-table
      :headers="$headers"
      :rows="$transacciones"
      empty-message="No hay transacciones pendientes."
      >

      @scope('cell_usuario', $t)
        <p class="text-xl font-bold">{{ $t->user->name }}</p>
        <p class="text-sm text-base-content/50">{{ $t->updated_at->diffForHumans() }}</p>
        <p class="text-xs text-base-content/50">{{ $t->notas }}</p>
      @endscope

      @scope('cell_tipo', $t)
        <x-badge class="badge-sm badge-{{ $t->tipo->color() }}" value="{{ $t->tipo->label() }}" />
      @endscope

      @scope('cell_estado', $t)
        <x-badge class="badge-sm badge-{{ $t->estado->color()}}" value="{{ $t->estado->label() }}" />
      @endscope

      @scope('cell_monto', $t)
        <span class="{{ $t->tipo == TipoTransaccion::RETIRO ? 'text-error' : 'text-success' }}">
          $ {{ Number::format($t->monto, 2) }}
        </span>
      @endscope

      @scope('actions', $t)
        <div class="flex gap-2">
          <x-button
            class="btn-info btn-sm btn-ghost"
            icon="o-magnifying-glass"
            link="{{ route('banco.show', $t) }}"
            />
        </div>
      @endscope
    </x-table>

    @if ($transacciones->isEmpty())
      <p class="text-center py-4">No hay transacciones pendientes.</p>
    @endif
  </div>
</div>
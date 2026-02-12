<?php

use App\Models\User;
use Livewire\Component;
use App\Models\Transaccion;

new class extends Component
{
  public $transacciones;
  public $headers;
  public $user;

  public function mount(User $user = null) {
    $this->user = $user ?? auth()->user();

    $this->transacciones = Transaccion::where('user_id', $this->user->id)
      ->orderBy('created_at')
      ->get();
    $this->headers = [
      ['key' => 'id', 'label' => 'Movimiento'],
      ['key' => 'monto', 'label' => 'Monto', 'class' => "w-32 text-right"],
    ];
  }
};
?>

<div>
  <x-title title="Banco" />
  @if ($user !== auth()->user())
    <x-alert class="mb-4 alert-warning text-lg">Estado de Cuenta de <span class="font-bold">{{ $user->name }}</span></x-alert>
  @endif

  <div class="bg-base-300 p-4 rounded shadow-md mb-4">
    <p>Tu saldo actual es:</p>
    <p class="text-2xl font-bold">$ {{ Number::format($user->saldo,2) }}</p>
  </div>

  <div class="flex gap-1 items-center mb-4">
    <x-button
      label="Depósito"
      icon="fas.plus-circle"
      class="btn-success"
      link="{{ route('banco.deposito') }}"
      />
    @if ($user->saldo > 0)
      <x-button
        label="Retiro"
        icon="fas.minus-circle"
        class="btn-error"
        link="{{ route('banco.retiro') }}"
        />
    @endif
  </div>

  <div class="rounded overflow-hidden shadow-md bg-base-100 border border-base-300">
    <x-table :headers="$headers" :rows="$transacciones">
      @scope('cell_id', $t)
        <p>{{ $t->created_at }}</p>
        <p class="flex gap-1 items-center">
          <x-badge value="{{ $t->tipo->label() }}" class="badge-{{ $t->tipo->color() }} badge-sm" />
          <x-badge value="{{ $t->estado->label() }}" class="badge-{{ $t->estado->color() }} badge-sm" />
        </p>
        <p class="text-xs text-base-content/50">{{ $t->notas }}</p>
      @endscope

      @scope('cell_monto', $t)
        <p class="font-mono text-lg {{ $t->monto > 0 ? 'text-success' : 'text-error' }}">{{ Number::format($t->monto, 2) }}</p>
      @endscope

      @scope('actions', $t)
        <x-button
          label="Detalles"
          icon="fas.magnifying-glass"
          class="btn-ghost btn-sm btn-primary"
          link="{{ route('banco.show', $t) }}"
          />
      @endscope
    </x-table>
  </div>
</div>
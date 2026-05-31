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
  <x-title
    title="Banco"
    subtitle="Gestiona tus fondos y revisa tus movimientos"
    icon="fas.wallet"
    />
  @if ($user !== auth()->user())
    <x-alert class="mb-4 alert-warning text-lg">Estado de Cuenta de <span class="font-bold">{{ $user->name }}</span></x-alert>
  @endif

  <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
    <div class="md:col-span-2">
      <div class="bg-base-100 p-4 shadow-md mb-4 border-base-300 border rounded-lg">
        {{-- Saldos --}}
        <div class="text-center mb-6">
          <p class="text-xl font-bold tracking-widest text-base-content/75">SALDO TOTAL DISPONIBLE</p>
          <p class="text-4xl text-accent font-bold">$ {{ Number::format($user->saldo - $user->retiros_pendientes,2) }}</p>
        </div>

        {{-- Botones depósito/retiro --}}
        <div class="grid grid-cols-2 gap-1 w-full">
          <x-button
            class="btn-success h-18"
            link="{{ route('banco.deposito') }}"
            >
            <div>
              <div><x-icon name="fas.plus-circle" class="w-6 h-6 mb-2" /></div>
              <div class="font-bold tracking-widest">DEPÓSITO</div>
            </div>
          </x-button>
          @if ($user->saldo > 0)
            <x-button
              class="btn-error h-18"
              link="{{ route('banco.retiro') }}"
              >
              <div>
                <div><x-icon name="fas.minus-circle" class="w-6 h-6 mb-2" /></div>
                <div class="font-bold tracking-widest">RETIRO</div>
              </div>
            </x-button>
          @endif
        </div>
      </div>

      @if ($user->retiros_pendientes > 0)
        <div class="bg-base-100/50 p-4 shadow-md mb-4 border-base-300 border rounded-lg text-center">
          <p class="text-sm text-base-content/75">Retiros Pendientes:</p>
          <p class="text-2xl font-bold">$ {{ Number::format($user->retiros_pendientes,2) }}</p>
        </div>
      @endif
    </div>

    <div class="rounded-lg overflow-hidden shadow-md bg-base-100 border border-base-300 grid-cols-1 md:col-span-4">
      <div class="p-4">
        <h1 class="text-xl font-bold">Movimientos Recientes</h1>
        <p class="text-xs text-base-content/50">Haz click sobre cualquier transaccion para ver el detalle</p>
      </div>
      <x-table
        :headers="$headers"
        :rows="$transacciones"
        :link="route('banco.show', ['transaccion' => '[id]'])"
        >
        @scope('cell_id', $t)
          <p>{{ $t->created_at }}</p>
            <x-badge value="{{ $t->estado->label() }}" class="badge-{{ $t->estado->color() }} badge-sm" />
          </p>
          <p class="text-xs text-base-content/50">{{ $t->notas }}</p>
        @endscope

        @scope('cell_monto', $t)
          <p class="font-mono text-lg {{ $t->monto > 0 ? 'text-success' : 'text-error' }}">{{ Number::format($t->monto, 2) }}</p>
        @endscope
      </x-table>
    </div>
  </div>




</div>
<?php

use App\Enums\EventoStatus;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $boletos;
  public $open = false;

  // Formulario de Participación
  public $nombre;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->boletos = $evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }

  public function comprar() {
    $num = Participacion::where('evento_id', $this->evento->id)
      ->where('user_id', auth()->id())
      ->count();
    $this->nombre = auth()->user()->displayName . ' #' . sprintf("%02d", $num + 1);
    $this->open = true;
  }

  public function confirmarCompra() {
    $this->validate([
      'nombre' => 'required|string|max:255',
    ]);

    // Verificar que el usuario tenga saldo suficiente
    if (auth()->user()->saldo < $this->evento->precio) {
      $this->error(
        title: 'Saldo insuficiente',
        description: 'No tienes saldo suficiente para comprar este boleto. Por favor recarga tu saldo e intenta de nuevo.',
        icon: 'fas.credit-card',
        redirectTo: route('banco.deposito'),
      );
      return;
    }

    // Crear la participación
    $participacion = Participacion::create([
      'evento_id' => $this->evento->id,
      'user_id' => auth()->id(),
      'nombre' => $this->nombre,
    ]);

    // Descontar el saldo del usuario
    $transaccion = Transaccion::create([
      'user_id' => auth()->id(),
      'monto' => -$this->evento->precio,
      'tipo' => 'retiro',
      'descripcion' => "Compra de boleto para evento '{$this->evento->nombre}'",
    ]);


  }
};
?>

<div class="bg-base-100 rounded-lg shadow-xl overflow-hidden border border-gray-300 dark:border-gray-600 flex flex-col h-full">

  <x-modal wire:model='open'>
    <p class="mb-6">El siguiente nombre es con el que puedes referirte a este boleto, también es el que se presentará en los tableros de líderes del evento. Puedes modificarlo, pero una vez comprado, no podrás cambiar el nombre</p>
    <x-form wire:submit='confirmarCompra'>
      <x-input
        wire:model='nombre'
        label="Nombre del boleto"
        class="outline-none! w-full"
        placeholder="{{ $nombre }}"
        required
        inline
        />
      <div class="flex gap-1 items-center justify-end mt-4">
        <x-button
          label="Confirmar compra"
          icon="fas.circle-check"
          class="btn-primary"
          type="submit"
          />
        <x-button
          label="Cancelar"
          icon="fas.xmark"
          class="btn-ghost"
          wire:click='$set("open", false)'
          />
      </div>
    </x-form>
  </x-modal>

  <img src="{{ $evento->imagenUrl }}"
    alt="{{ $evento->temporada->deporte->nombre }}"
    class="w-full h-48 object-cover"
    >

  <div class="p-4 flex flex-col flex-1">
    <h2 class="text-xl text-accent uppercase tracking-wide font-bold">{{ $evento->nombre }}</h2>
    <p class="text-sm text-base-content/50">{{ $evento->temporada->nombre }}</p>
    <x-badge value="{{ $evento->estado->label() }}" class="badge-sm badge-{{ $evento->estado->color() }} mb-2" />

    <p>Precio: ${{ Number::format($evento->precio, 2) }}</p>
    @if ($evento->participaciones->count() > 0)
      <p class="text-info py-1"><x-icon name="fas.people-group"/> {{ $evento->participaciones->count() }} Participantes</p>
    @endif

    <p class="my-4 text-base-content/80">{{ $evento->descripcion }}</p>

    {{-- Boletos del usuario --}}
    <div class="mt-auto mb-2">
      @if ($boletos->count() > 0)
        <p class="text-xs text-base-content/50 mb-1">Tienes {{ $boletos->count() }} boleto{{ $boletos->count() > 1 ? 's' : '' }}</p>
        @foreach ($boletos as $boleto)
          <div class="p-2 mb-2 bg-secondary/20 rounded-lg flex items-center gap-2">
            <x-icon name="fas.ticket" class="text-secondary" />
            <p class="text-sm text-secondary">#{{ sprintf("%05d",$boleto->id) }} {{ $boleto->nombre }}</p>
          </div>
        @endforeach
      @endif
    </div>

    {{-- Botones de acción --}}
    <div class="flex gap-1 items-center justify-end">
      <x-button
        link="#"
        icon="fas.info-circle"
        label="Detalles"
        class="btn-ghost btn-info"
        />
      @if (auth()->user()->saldo >= $evento->precio)
        @if ($evento->estado === EventoStatus::ACTIVO)
          <x-button
            icon="fas.cart-shopping"
            class="btn-primary"
            label="Comprar"
            wire:click='comprar'
            />
        @endif
      @else
        <x-button
          link="{{ route('banco.deposito') }}"
          icon="fas.credit-card"
          class="btn-error"
          label="Recargar saldo"
          />
      @endif
    </div>
  </div>
</div>

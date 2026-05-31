<?php

use App\Enums\EventoStatus;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
  use Mary\Traits\Toast;

  public Evento $evento;
  public $boletos;

  public $modalCodigo = false;
  public $codigo;

  public $modalComprar = false;
  public $nombre = '';

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->boletos = $evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }

  public function comprar() {
    // Revisar si el evento tiene un máximo de cupos y si ya se alcanzó
    if ($this->evento->cupos && $this->evento->participaciones()->count() >= $this->evento->cupos) {
      $this->error(
        title: 'Evento lleno',
        description: 'Lo sentimos, este evento ya ha alcanzado el máximo de participantes. Por favor mantente atendo a que se abran nuevos cupos',
        icon: 'fas.people-group',
      );
      return;
    }

    // Revisar si hay un máximo de participaciones por usuario y si ya lo alcanzó
    if ($this->evento->boleto_maximo) {
      $participacionesUsuario = $this->evento
        ->participaciones()
        ->where('user_id',
        auth()->id())->count();
      if ($participacionesUsuario >= $this->evento->boleto_maximo) {
        $this->error(
          title: 'Límite de boletos alcanzado',
          description: 'Has alcanzado el límite de boletos para este evento. No puedes comprar más boletos.',
          icon: 'fas.ticket',
        );
        return;
      }
    }

    // Si el evento tiene código, abrir el modal para ingresar el código
    if ($this->evento->codigo) {
      $this->codigo = '';
      $this->modalCodigo = true;
      return;
    }

    $this->nombre = auth()->user()->displayName . ' #' . sprintf("%02d", $this->evento->participaciones()->where('user_id', auth()->id())->count() + 1);
    $this->modalComprar = true;
  }

  public function verificarCodigo() {
    $this->validate([
      'codigo' => 'required|string',
    ]);

    if ($this->codigo !== $this->evento->codigo) {
      $this->error(
        title: 'Código incorrecto',
        description: 'El código ingresado no es correcto. Por favor intenta de nuevo.',
        icon: 'fas.xmark',
      );
      return;
    }
    $this->modalCodigo = false;

    $this->nombre = auth()->user()->displayName . ' #' . sprintf("%02d", $this->evento->participaciones()->where('user_id', auth()->id())->count() + 1);
    $this->modalComprar = true;
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
      'tipo' => 'compra',
      'estado' => 'aprobada',
      'descripcion' => "Compra de boleto para evento '{$this->evento->nombre}'",
    ]);

    $this->success(
      title: 'Compra exitosa',
      description: "Has comprado el boleto '{$this->nombre}' para el evento '{$this->evento->nombre}'. ¡Buena suerte!",
      icon: 'fas.check',
    );
    $this->modalComprar = false;

    // Actualizar el evento para reflejar el nuevo número de participaciones
    $this->evento = Evento::find($this->evento->id);
    $this->boletos = $this->evento
      ->participaciones()
      ->where('user_id', auth()->id())
      ->get();
  }
};
?>

<div class="bg-base-100 rounded-lg shadow-xl overflow-hidden border border-gray-300 dark:border-gray-600 flex flex-col h-full">

  {{-- Ingresar el código del evento --}}
  <x-modal
    wire:model="modalCodigo"
    class="backdrop-blur-md"
    >
    Este evento requiere un código para poder comprarlo. Por favor ingresa el código proporcionado.
    <form wire:submit='verificarCodigo'>
      <x-input
        wire:model="codigo"
        label="Código del evento"
        placeholder="Ingresa el código aquí"
        class="outline-none!"
        required
        />

      <div class="modal-action">
        <x-button
          type="submit"
          icon="fas.check"
          label="Verificar código"
          class="btn-primary"
          />
        <x-button
          type="button"
          icon="fas.xmark"
          label="Cancelar"
          class="btn-ghost"
          wire:click="$set('modalCodigo', false)"
          />
      </div>
    </form>
  </x-modal>

  {{-- Comprar el evento --}}
  <x-modal
    wire:model="modalComprar"
    class="backdrop-blur-md"
    >
    El siguiente nombre es con el que puedes referirte a este boleto, también es el que se presentará en los tableros de líderes del evento. Puedes modificarlo, pero una vez comprado, no podrás cambiar el nombre
    <x-form wire:submit='confirmarCompra'>
      <x-input
        wire:model='nombre'
        label="Nombre del boleto"
        class="outline-none!"
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
          spinner="confirmarCompra"
          />
        <x-button
          label="Cancelar"
          icon="fas.xmark"
          class="btn-ghost"
          wire:click="$set('modalComprar', false)"
          spinner="cancelar"
          />
      </div>
    </x-form>
  </x-modal>

  <img src="{{ $evento->imagenUrl }}"
    alt="{{ $evento->temporada->deporte->nombre }}"
    class="w-full h-48 md:h-72 object-cover"
    >

  <div class="p-4 flex flex-col flex-1">
    <h2 class="text-xl text-accent uppercase tracking-wide font-bold font-display">{{ $evento->nombre }}</h2>
    <p class="text-xs text-base-content/50 mb-2">{{ $evento->temporada->nombre }}</p>

    <div class="flex gap-2 mb-2">
      <x-badge value="{{ $evento->estado->label() }}" class="badge-sm badge-{{ $evento->estado->color() }} uppercase" />
      <x-badge value="{{ $evento->tipojuego->nombre }}" class="badge-sm badge-secondary font-bold uppercase tracking-wide" />
    </div>

    <p>Precio: ${{ Number::format($evento->precio, 2) }}</p>
    @if ($evento->participaciones->count() > 0)
      <p class="text-info py-1"><x-icon name="fas.people-group"/> {{ $evento->participaciones->count() }} Participantes</p>
    @endif

    <p class="text-xs text-base-content/50 mt-2">Tienes {{ $boletos->count() }} boleto{{ $boletos->count() > 1 ? 's' : '' }}</p>
    @if ($boletos->count() > 0)
      <div class="mt-auto pb-2">
        @foreach ($boletos as $boleto)
            <x-button
              link="{{ route(strtolower($boleto->evento->temporada->deporte_id) . '.' . $boleto->evento->tipojuego_id . '.pronosticos', [$boleto->evento, 'p' => $boleto->id]) }}"
              class="btn-sm btn-secondary w-full mb-2"
              >
              <div class="flex justify-between gap-2 items-center w-full">
                <div class="flex gap-1 items-center">
                  <x-icon name="fas.ticket" class="text-secondary-content" />
                  <p class="text-sm text-secondary-content">#{{ sprintf("%05d",$boleto->id) }} {{ $boleto->nombre }}</p>
                </div>

                <x-icon name="fas.play" class="w-3 h-3" />
              </div>
            </x-button>
        @endforeach
      </div>
    @endif

    {{-- Botones de acción --}}
    <div class="flex gap-1 items-center justify-end mt-auto">
      <x-button
        link="{{ route('evento.show', $evento) }}"
        icon="fas.info-circle"
        label="Detalles"
        class="btn-ghost btn-info"
        />
      @if ($evento->estado === EventoStatus::ACTIVO)
        @if (auth()->user()->saldo >= $evento->precio)
          <x-button
            icon="fas.cart-shopping"
            class="btn-primary"
            label="Comprar"
            wire:click='comprar'
            spinner="comprar"
            />
        @else
          <x-button
            link="{{ route('banco.deposito') }}"
            icon="fas.credit-card"
            class="btn-error"
            label="Recargar saldo"
            />
        @endif
      @endif
    </div>
  </div>
</div>

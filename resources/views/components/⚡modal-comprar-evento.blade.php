<?php

use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public Evento $evento;
  public $open = false;
  public $nombre='';

  public function mount(Evento $evento) {
    $this->evento = $evento;
  }

  #[On('open-modal-comprar')]
  public function comprar($eventoId) {
    info("Evento de componente: " . $this->evento->id);
    info("Evento recibido: ". $eventoId);

    if ($this->evento->id != $eventoId) {
      info("Dios es caca");
      return;
    }

    info("Dios es mierda");

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
      'tipo' => 'compra',
      'estado' => 'autorizada',
      'descripcion' => "Compra de boleto para evento '{$this->evento->nombre}'",
    ]);

    $this->success(
      title: 'Compra exitosa',
      description: "Has comprado el boleto '{$this->nombre}' para el evento '{$this->evento->nombre}'. ¡Buena suerte!",
      icon: 'fas.check',
    );
    $this->open = false;
  }

  public function cancelar() {
    $this->open = false;
  }

};
?>

<x-modal wire:model='open'>
  <p class="mt-4 mb-6">El siguiente nombre es con el que puedes referirte a este boleto, también es el que se presentará en los tableros de líderes del evento. Puedes modificarlo, pero una vez comprado, no podrás cambiar el nombre</p>
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
        spinner="confirmarCompra"
        />
      <x-button
        label="Cancelar"
        icon="fas.xmark"
        class="btn-ghost"
        wire:click='cancelar'
        spinner="cancelar"
        />
    </div>
  </x-form>
</x-modal>

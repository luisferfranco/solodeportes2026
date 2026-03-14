<?php

use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Transaccion;
use Livewire\WithFileUploads;
use App\Notifications\BankProcessedNotification;

new class extends Component
{
  use Toast;
  use WithFileUploads;

  public $transaccion;
  public $notas;
  public $file;

  public function mount(Transaccion $transaccion) {
    $this->authorize('view', $transaccion);

    $this->transaccion = $transaccion;
    $this->notas = $transaccion->notas;
  }

  public function procesar($estado) {

    $this->validate([
      'notas' => 'nullable|string|max:255',
      'file' => 'nullable|image|max:2048', // Máximo 2MB
    ]);

    if ($this->file) {
      $comprobantePath = $this->file->store('comprobantes', 'public');
      $this->transaccion->comprobante = $comprobantePath;
    }
    $this->transaccion->estado = $estado;
    $this->transaccion->notas = $this->notas;
    $this->transaccion->save();

    $this->transaccion->user->notify(new BankProcessedNotification(auth()->user(), $this->transaccion));

    $this->success(
      title: 'Transacción Procesada',
      description: 'La transacción ha sido ' . ($estado === 'aprobada' ? 'aprobada y el saldo actualizado' : 'rechazada y el usuario notificado'),
      icon: 'fas.check-circle',
      timeout: 5000,
    );

    $this->redirectIntended(route('banco'));
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
        <div class="w-full md:w-96 rounded shadow-md overflow-hidden shrink-0">
          <img
            src="{{ asset('/storage/' . $transaccion->comprobante) }}"
            alt="Comprobante"
            class="w-full h-auto rounded shadow-md"
            >
        </div>
      @endif

      <div class="w-full">
        <x-card>
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

        @can('autorizar', $transaccion)
          <x-card class="mt-4">

            <x-alert
              title="Pendiente de autorización"
              class="alert-warning mb-4"
              icon="fas.clipboard-check"
              />

            <p class="font-bold">{{ $transaccion->user->name }}</p>
            <p class="text-sm text-base-content/50 mb-6">{{ $transaccion->clabe ?? $transaccion->user->clabe }}</p>

            <x-textarea
              label="Notas para el usuario"
              placeholder="Agrega una nota que el usuario verá al autorizar o rechazar la transacción"
              wire:model="notas"
              rows="5"
              class="outline-none!"
              />
            <x-file
              label="Comprobante de la operación (opcional)"
              accept="image/*"
              wire:model="file"
              inline
              />
            <div class="flex gap-2 mt-4">
              <x-button
                label="Autorizar"
                icon="fas.check"
                class="btn-success"
                wire:click="procesar('aprobada')"
                />
              <x-button
                label="Rechazar"
                icon="fas.xmark"
                class="btn-error"
                wire:click="procesar('rechazada')"
                />
            </div>

          </x-card>
        @endcan

      </div>
    </div>

  </div>



</div>
<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Transaccion;
use Livewire\WithFileUploads;
use App\Notifications\BankNotification;

new class extends Component
{
  use Toast;
  use WithFileUploads;

  public $monto = 0;
  public $file;
  public $notas;

  public function save() {
    info($this->monto);
    info($this->file);
    info($this->notas);

    $this->validate([
      'monto' => 'required|numeric|min:1',
      'file'  => 'required|image|max:2048',
      'notas' => 'nullable|string|max:500',
    ]);

    $file = $this->file->store('comprobantes', 'public');

    $transaccion = Transaccion::create([
      'user_id'     => auth()->id(),
      'monto'       => $this->monto,
      'tipo'        => 'deposito',
      'estado'      => 'pendiente',
      'notas'       => $this->notas,
      'comprobante' => $file,
    ]);

    User::where('nivel', 99)
      ->get()
      ->each(fn($admin) => $admin->notify(new BankNotification(auth()->user(), $transaccion)));

    $this->success(
      title: 'Depósito Enviado',
      description: 'Tu depósito ha sido enviado y está pendiente de verificación.',
      icon: 'fas.circle-check',
      timeout: 3000,
      redirectTo: route('banco')
    );

  }
};
?>

<div>
  <x-title title="Depósito" />

  <div class="max-w-4xl mx-auto">
    <x-card class="bg-info text-info-content mb-4">
      <p class="flex gap-1 text-base-content items-center mb-2">
        <x-icon name="fas.info-circle" />
        <span class="font-bold">Información Importante</span>
      </p>
      <ul class="list-disc list-outside pl-6">
        <li>Por favor realiza tu depósito a La CLABE <span class="font-bold">6381 8001 0138 7650 52</span> de Banco Nu a nombre de <span class="font-bold">Luis Fernando Franco Jiménez</span></li>
        <li>Adjunta el comprobante de tu transferencia como un archivo de imagen</li>
        <li>Los depósitos se reflejarán una vez que hayan sido verificados</li>
      </ul>
    </x-card>

    <x-card class="bg-base-100 text-base-content">
      <x-form wire:submit='save'>
        <div class="flex gap-2 justify-between">
          <x-input
            wire:model='monto'
            label="Monto a depositar"
            class="outline-none! w-full"
            numeric
            required
            prefix="MXP$"
            />
          <x-file
            wire:model='file'
            label="Comprobante de depósito"
            class="outline-none! w-full"
            accept="image/*"
            required
            />
        </div>
        <x-textarea
          wire:model='notas'
          label="Notas adicionales (opcional)"
          placeholder="Ejemplo: Depósito realizado el 01/01/2024 a las 10:00 AM"
          rows="5"
          />
        <div class="flex gap-1 items-center">
          <x-button
            label="Enviar Depósito"
            icon="fas.circle-check"
            class="btn-primary"
            type="submit"
            />
          <x-button
            label="Cancelar"
            icon="fas.xmark"
            class="btn-ghost btn-error"
            link="{{ route('banco') }}"
            />
        </div>


      </x-form>

    </x-card>
  </div>
</div>
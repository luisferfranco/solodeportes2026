<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Transaccion;
use App\Notifications\BankNotification;

new class extends Component
{
  use Toast;

  public $saldo;
  public $pendientes;
  public $monto;
  public $clabe;
  public $notas;

  public function mount() {
    $this->saldo = auth()->user()->saldo;
    $this->clabe = auth()->user()->clabe;
    $this->pendientes = auth()->user()->transacciones()
      ->where('tipo', 'retiro')
      ->where('estado', 'pendiente')
      ->sum('monto');
  }

  public function retiro() {
    $this->validate([
      'monto' => 'required|numeric|min:1|max:' . ($this->saldo + $this->pendientes),
      'clabe' => 'required|digits:18',
      'notas' => 'nullable|string|max:500',
    ]);

    $transaccion = Transaccion::create([
      'user_id' => auth()->id(),
      'monto'   => -$this->monto,
      'tipo'    => 'retiro',
      'estado'  => 'pendiente',
      'notas'   => $this->notas,
      'clabe'   => $this->clabe,
    ]);

    $user = auth()->user();
    $user->clabe = $this->clabe;
    $user->save();

    User::where('nivel', 99)
      ->get()
      ->each(fn($admin) => $admin->notify(new BankNotification(auth()->user(), $transaccion)));

    $this->success(
      title: 'Retiro Solicitado',
      description: 'Tu retiro ha sido solicitado y está pendiente de procesamiento',
      icon: 'fas.circle-check',
      timeout: 3000,
      redirectTo: route('banco')
    );
  }
};
?>

<div>
  <x-title title="retiros" />

  <div class="max-w-4xl mx-auto">
    <x-card class="bg-info text-info-content mb-4">
      <div class="grid grid-cols-2">
        <div class="text-xl">Tu Saldo en Firme</div>
        <div class="text-xl text-right font-mono">$ {{ Number::format($saldo, 2) }}</div>

        <div class="text-xl">Retiros Pendientes</div>
        <div class="text-xl text-right font-mono">$ {{ Number::format($pendientes, 2) }}</div>

        <div class="text-xl">Puedes solictar hasta</div>
        <div class="text-xl font-bold text-right font-mono">$ {{ Number::format($saldo + $pendientes, 2) }}</div>
      </div>
    </x-card>

    <x-card class="bg-warning text-warning-content mb-4">
      <p class="flex gap-1 items-center mb-2">
        <x-icon name="fas.triangle-exclamation" />
        <span class="font-bold">Información Importante</span>
      </p>
      <ul class="list-disc list-outside pl-6">
        <li>Los retiros se procesan manualmente y pueden tardar hasta 48 horas en reflejarse.</li>
        <li>Revisa que tus datos bancarios sean correctos (CLABE)</li>
      </ul>
    </x-card>

    <x-card>
      <x-form wire:submit='retiro'>
        <div class="flex gap-1 justify-between items-start">
          <div class="w-full">
            <x-input
              wire:model='monto'
              label="Monto a retirar"
              class="outline-none!"
              numeric
              required
              prefix="MXP$"
              />
          </div>
          <div class="w-full">
            <x-input
              wire:model='clabe'
              label="CLABE Interbancaria"
              class="outline-none!"
              required
              />
          </div>
        </div>
        <x-textarea
          wire:model='notas'
          label="Notas (opcional)"
          class="outline-none!"
          rows="5"
          maxlength="500"
          />
        <div class="flex gap-1 items-center">
          <x-button
            label="Solicitar Retiro"
            icon="fas.check-circle"
            type="submit"
            class="btn-primary"
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
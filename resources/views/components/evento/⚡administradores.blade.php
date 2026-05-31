<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\User;

new class extends Component
{
  public Evento $evento;
  public $headers;
  public $users, $user = null;

  public function mount(Evento $evento) {
    $this->evento   = $evento;
    $this->users    = User::orderBy('name')->get();
    $this->headers  = [
      ['key' => 'name', 'label' => 'Nombre'],
      ['key' => 'pivot.rol', 'label' => 'Rol'],
    ];
  }

  public function addAdmin() {
    if (!$this->user) return;

    $this->evento->administradores()->attach($this->user, ['rol' => 'coadmin']);
    $this->user = null;
  }

  public function removeAdmin($userId) {
    $this->evento->administradores()->detach($userId);
  }
};
?>

<x-card class="bg-base-100">
  <h1 class="text-xl font-bold">Administradores</h1>
  <div class="flex gap-2 w-full">

    <div class="w-full flex-1">
      <x-select
        :options="$users"
        class="outline-none"
        placeholder="Selecciona un usuario"
        wire:model.live="user"
        />
    </div>

    <x-button
      class="btn-primary"
      wire:click="addAdmin"
      icon="s-plus-circle"
      label="Agregar"
      spinner
      />
  </div>

  <x-table
    :headers="$headers"
    :rows="$evento->administradores"
    >
    @scope('cell_pivot.rol', $row)
      <div class="flex gap-1">
        @if ($row->pivot->rol === 'admin')
          <x-icon name="s-star" class="text-yellow-500 w-5 h-5" />
        @endif
        <x-badge
          class="{{ $row->pivot->rol == 'admin' ? 'badge-error' : 'badge-info' }} uppercase tracking-wide"
          value="{{ $row->pivot->rol }}"
          />
      </div>
    @endscope

    @scope('actions', $row)
      @if ($row->pivot->rol != 'admin')
        <x-button
          class="btn-ghost btn-error btn-sm"
          wire:click="removeAdmin({{ $row->id }})"
          icon="s-trash"
          />
     @endif
    @endscope
  </x-table>
</x-card>

<?php

use App\Models\User;
use Livewire\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    public User $user;

    public int $nivel;

    public array $niveles = [
        ['id' => 0, 'name' => 'Usuario inactivo o suspendido'],
        ['id' => 1, 'name' => 'Usuario normal'],
        ['id' => 50, 'name' => 'Administrador'],
        ['id' => 99, 'name' => 'Superadministrador'],
    ];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->nivel = $user->nivel;
    }

    public function save(): void
    {
        $this->validate([
            'nivel' => 'required|integer|in:0,1,50,99',
        ]);

        $this->user->update(['nivel' => $this->nivel]);

        $this->success('Nivel de usuario actualizado correctamente');
    }

    public function with(): array
    {
        return [
            'niveles' => $this->niveles,
        ];
    }
};
?>

<div>
  <x-title :title="$user->displayName" subtitle="Detalle del usuario" />

  <!-- USER INFO CARD -->
  <x-card class="mt-6 mb-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center gap-4">
        <x-avatar
          :image="$user->avatar"
          class="h-16 w-16"
        />
        <div>
          <p class="text-lg font-semibold">{{ $user->displayName }}</p>
          <p class="text-sm text-gray-500">{{ $user->email }}</p>
          <p class="mt-1 text-xs text-gray-500">
            Registrado el {{ $user->created_at->format('d/m/Y H:i') }}
          </p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-xs uppercase text-gray-500">Participaciones</p>
        <p class="text-2xl font-bold">{{ $user->participaciones_count ?? $user->participaciones()->count() }}</p>
      </div>
    </div>
  </x-card>

  <!-- EDIT FORM -->
  <x-form wire:submit="save">
    <x-select
      wire:model="nivel"
      label="Nivel de usuario"
      hint="Define el rol y permisos del usuario"
      :options="$niveles"
      option-label="name"
      option-value="id"
      class="outline-none!"
      required
    />

    <x-slot:actions>
      <x-button
        label="Volver"
        icon="o-arrow-left"
        link="{{ route('admin.users.index') }}"
      />
      <x-button
        label="Guardar"
        icon="o-check"
        class="btn-primary"
        type="submit"
      />
    </x-slot:actions>
  </x-form>
</div>
<?php

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component
{
    use WithPagination;
    use Toast;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public array $userNivel = [];

    public bool $onlyWithSaldo = false;

    public $options;

    public array $niveles = [
        ['id' => 0, 'name' => 'Usuario inactivo o suspendido'],
        ['id' => 1, 'name' => 'Usuario normal'],
        ['id' => 50, 'name' => 'Administrador'],
        ['id' => 99, 'name' => 'Superadministrador'],
    ];

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Nombre'],
            ['key' => 'participaciones_count', 'label' => 'Participaciones', 'class' => 'w-24 text-center'],
            ['key' => 'saldo', 'label' => 'Saldo', 'class' => 'w-32 text-right'],
            ['key' => 'actions', 'label' => 'Acciones', 'class' => 'w-80'],
        ];
    }

    public function users()
    {
        return User::query()
            ->withCount('participaciones')
            ->withSum(['transacciones as saldo_sum' => function ($q) { $q->where('estado', 'aprobada'); }], 'monto')
            ->when($this->search, function ($query) {
                $query->whereAny(['name', 'email'], 'like', "%{$this->search}%");
            })
            ->when($this->onlyWithSaldo, function ($query) {
                $query->whereRaw("(SELECT COALESCE(SUM(monto),0) FROM transacciones WHERE transacciones.user_id = users.id AND transacciones.estado = ?) > 0", ['aprobada']);
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(15);
    }

    public function getTotalSaldo()
    {
        $usersQ = User::query()
            ->when($this->search, function ($query) {
                $query->whereAny(['name', 'email'], 'like', "%{$this->search}%");
            });

        if ($this->onlyWithSaldo) {
            $usersQ->whereRaw("(SELECT COALESCE(SUM(monto),0) FROM transacciones WHERE transacciones.user_id = users.id AND transacciones.estado = ?) > 0", ['aprobada']);
        }

        $ids = $usersQ->pluck('id')->toArray();

        if (empty($ids)) {
            return 0;
        }

        return \DB::table('transacciones')
            ->whereIn('user_id', $ids)
            ->where('estado', 'aprobada')
            ->sum('monto');
    }

    public function with(): array
    {
        $users = $this->users();
        $totalSaldo = $this->getTotalSaldo();

        // Inicializar userNivel con los niveles reales de los usuarios
        foreach ($users as $user) {
            $this->userNivel[$user->id] = $user->nivel;
        }

        return [
            'users' => $users,
            'headers' => $this->headers(),
            'niveles' => $this->niveles,
            'totalSaldo' => $totalSaldo,
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedOnlyWithSaldo($value)
    {
        $this->resetPage();
    }

    public function updateNivel($userId, $nivel): void
    {
        User::findOrFail($userId)->update(['nivel' => $nivel]);
        $this->success('Nivel actualizado correctamente');
    }

    public function updated($propertyName, $value): void
    {
        if (str_starts_with($propertyName, 'userNivel.')) {
            $userId = (int) str_replace('userNivel.', '', $propertyName);
            $this->updateNivel($userId, $value);
        }
    }

    public function resetPassword($userId): void
    {
        User::findOrFail($userId)->update(['password' => bcrypt('password')]);
        $this->success('Password reseteado a "password"');
    }

    public function deleteUser($userId): void
    {
        User::findOrFail($userId)->delete();
        $this->success('Usuario eliminado correctamente');
    }
};
?>

<div>
  <x-title title="Usuarios" subtitle="Administración de usuarios" />

  <!-- STATS -->
  <div class="mt-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <x-stat
        title="Saldo Total"
        description="Suma de todos los saldos"
        :value="'$' . number_format($totalSaldo, 2)"
        icon="o-currency-dollar"
        class="bg-sky-200 dark:bg-sky-600"
      />
      <x-stat
        title="Total Usuarios"
        description="Usuarios activos en el sistema"
        :value="$users->total()"
        icon="o-users"
        class="bg-green-200 dark:bg-green-600"
      />
    </div>
  </div>

  <!-- TOGGLE: mostrar sólo con saldo -->
  <div class="mt-4">
    <x-toggle wire:model.live="onlyWithSaldo" label="Mostrar sólo usuarios con saldo" />
  </div>

  <!-- FILTER INPUT -->
  <div class="mt-6">
    <x-input
      placeholder="Buscar por nombre o correo..."
      wire:model.live.debounce="search"
      clearable
      class="outline-none!"
      icon="o-magnifying-glass"
    />
  </div>

  <x-card shadow class="mt-6">
    <x-table
      :headers="$headers"
      :rows="$users"
      :sort-by="$sortBy"
      with-pagination
      >
      @scope('cell_name', $row)
        <x-avatar
          :image="$row->avatar"
          class="h-10 w-10"
          :title="$row->displayName"
          :subtitle="$row->email"
          >
          <x-slot:title>
            <a href="{{ route('admin.users.show', $row) }}" class="hover:underline">{{ $row->displayName }}</a>
          </x-slot:title>
        </x-avatar>
      @endscope

      @scope('cell_participaciones_count', $row)
        <span class="inline-flex items-center justify-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
          {{ $row->participaciones_count }}
        </span>
      @endscope

      @scope('cell_saldo', $row)
        <a
          href="{{ route('banco', $row->id) }}"
          class="text-sm font-semibold text-right hover:underline hover:text-info"
          ><span class="text-sm font-semibold">{{ '$' . Number::format($row->saldo_sum ?? $row->saldo, 2) }}</span>
      </a>
      @endscope

      @scope('cell_actions', $row)
        <div class="flex items-center gap-2">
          <!-- NIVEL SELECT -->
          <div class="grow">
            <x-select
              wire:model.live="userNivel.{{ $row->id }}"
              :options="$this->niveles"
              class="outline-none! w-full"
              option-label="name"
              option-value="id"
              :value="$row->nivel"
              no-label
              />
          </div>

          <!-- RESET PASSWORD BUTTON -->
          <x-button
            icon="o-key"
            wire:click="resetPassword({{ $row->id }})"
            wire:confirm="¿Resetear password a 'password'?"
            class="btn-ghost btn-sm"
            title="Resetear password"
          />

          <!-- DELETE BUTTON (only if no participations) -->
          @if ($row->participaciones_count == 0)
            <x-button
              icon="o-trash"
              wire:click="deleteUser({{ $row->id }})"
              wire:confirm="¿Eliminar usuario?"
              class="btn-ghost btn-sm text-error"
              title="Eliminar usuario"
            />
          @endif
        </div>
      @endscope
    </x-table>
  </x-card>
</div>
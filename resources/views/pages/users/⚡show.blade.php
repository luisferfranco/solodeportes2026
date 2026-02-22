<?php

use App\Models\User;
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;

new class extends Component
{
  use Toast;
  use WithFileUploads;

  public ?User $user;
  public $openModal=false;
  public $name, $nick, $email, $password, $password_confirmation, $clabe;
  public $file;

  public function mount(?User $user = null)
  {
    $this->user   = $user ?? auth()->user();
  }

  public function editar() {
    $this->name   = $this->user->name;
    $this->nick   = $this->user->nick;
    $this->email  = $this->user->email;
    $this->clabe  = $this->user->clabe;
    $this->openModal = true;
  }

  public function save() {
    $this->validate([
      'name'      => 'required|string|max:255',
      'nick'      => 'nullable|string|max:255|unique:users,nick,' . $this->user->id,
      'email'     => 'required|email|unique:users,email,' . $this->user->id,
      'password'  => 'nullable|string|min:8|confirmed',
      'clabe'     => 'nullable|string|max:18',
      'file'      => 'nullable|image|max:2048', // max 2MB
    ]);

    $this->user->update([
      'name'      => $this->name,
      'nick'      => $this->nick,
      'email'     => $this->email,
      'clabe'     => $this->clabe,
      'password'  => $this->password ? bcrypt($this->password) : $this->user->password,
    ]);

    if ($this->file) {
      $this->user->update([
        'avatar' => $this->file->store('avatars', 'public'),
      ]);
    }

    $this->success('Perfil actualizado correctamente');
    $this->openModal = false;
  }
};
?>

<div>

  <x-modal
    wire:model='openModal'
    title="Editar Perfil"
    >
    <x-form wire:submit='save'>
      <x-input
        label="Nombre"
        wire:model='name'
        required
        class="outline-none!"
        icon="fas.user"
        />
      <p class="text-xs text-base-content/70 -mt-2">Por favor utiliza tu nombre real, ya que es el que usaremos para las transacciones bancarias. Si quieres algo más de privacidad, puedes poner tu "nickname" abajo, que es el nombre que se mostrará en todo el sitio y el que verán los demás jugadores</p>

      <x-input
        label="Nickname (nombre para mostrar)"
        wire:model='nick'
        class="outline-none!"
        icon="fas.user-secret"
        />

      <x-input
        label="Correo Electrónico"
        wire:model='email'
        required
        class="outline-none!"
        icon="fas.envelope"
        />
      <p class="text-xs text-base-content/70 -mt-2">Por favor utiliza un correo electrónico válido, este es el que utilizarás para entrar a la aplicación</p>

      <x-file
        label="Avatar"
        wire:model='file'
        accept="image/*"
        class="outline-none!"
        />
      <p class="text-xs text-base-content/70 -mt-2">Puedes subir una imagen para usar como avatar. Esta imagen se mostrará en el sitio para identtificarte. Deberá ser de menos de 2MB y en formato JPG, PNG o GIF</p>

      <x-input
        label="CLABE"
        wire:model='clabe'
        maxlength="18"
        icon="fas.money-bill-transfer"
        />
      <p class="text-xs text-base-content/70 -mt-2">Por favor verifica que tu nombre sea correcto, tal como aparece en la cuenta CLABE que esté registrada. Se utiliza para el depósito de premios</p>

      <hr />

      <p class="text-xs text-base-content/70 -mt-2">Si deseas cambiar tu contraseña, ingresa una nueva a continuación. Si la dejas vacía, se mantendrá la que tienes</p>
      <div class="flex gap-2">
        <x-input
          label="Contraseña"
          wire:model='password'
          type="password"
          class="outline-none!"
          icon="fas.lock"
          />
        <x-input
          label="Confirmar Contraseña"
          wire:model='password_confirmation'
          type="password"
          class="outline-none!"
          icon="fas.lock"
          />
      </div>

      <div class="flex gap-2">
        <x-button
          label="Guardar Cambios"
          icon="fas.circle-check"
          class="btn-primary mt-4"
          type="submit"
          />
        <x-button
          label="Cancelar"
          icon="fas.times"
          class="btn-secondary btn-ghost mt-4"
          wire:click="$set('edit', false)"
          />
      </div>
    </x-form>
  </x-modal>


  <x-title title="Perfil de Usuario" subtitle="{{ $user->name }}" />

  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="md:col-span-3">
      <div class="p-4 bg-base-100 rounded-xl shadow-md flex gap-2 items-start border-slate-400 dark:border-slate-600 border">
        <x-avatar :image="$user->avatarUrl" class="h-24 w-24" />
        <div>
          <p class="text-2xl font-bold">{{ $user->name }}</p>
          <p class="text-lg font-semibold">{{ $user->displayName }}</p>
          <p class="text-sm text-base-content/50">{{ $user->email }}</p>
          @if (auth()->id() === $user->id || auth()->user()->isAdmin)
            <p class="mt-4">CLABE: <span class="font-mono text-info">{{ $user->clabe ?? 'No registrada' }}</span></p>
            <p class="text-xs text-base-content/50">Requerimos tu CLABE para recibir premios por transferencia bancaria. Este dato solo se muestra a ti y a los administradores.</p>
            <x-button
              label="Editar Perfil"
              icon="fas.user-pen"
              class="btn-primary mt-4"
              wire:click='editar'
              />
          @endif
        </div>
      </div>
    </div>
    <div>
      <div class="p-4 bg-base-300 rounded-xl shadow-md border-slate-400 dark:border-slate-600 border">
        @if (auth()->id() === $user->id || auth()->user()->isAdmin)
          <p class="text-xl font-bold">Saldo</p>
          <p class="text-2xl font-bold font-mono text-right text-info">$ {{ Number::format($user->saldo,2) }}</p>
          <p class="text-xs text-base-content/50 mt-2">Este es el saldo disponible en tu cuenta para participar en los eventos. Puedes recargar saldo desde el banco o retirarlo a tu cuenta bancaria registrada. <span class="font-bold">Esta información no se muestra a otros jugadores.</span></p>
        @endif

        <div class="mt-4">
          <p class="text-xl font-bold">Boletos</p>
          @foreach ($user->participaciones as $p)
            <a
              href="{{ auth()->user()->id == $user->id ? route(strtolower($p->evento->temporada->deporte_id) . '.' . $p->evento->tipojuego_id . '.pronosticos', ['evento' => $p->evento, 'p' => $p]) : '#' }}"
              class="flex items-center py-1 px-2 mb-2 text-sm bg-secondary text-secondary-content rounded-lg shadow-md gap-1 truncate text-ellipsis text-left hover:bg-accent hover:text-accent-content transition duration-200">
              <x-icon name="{{ $p->evento->deporte->icono }}" class="w-4 h-4" />
              <span>{{ $p->evento->nombre }}</span>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
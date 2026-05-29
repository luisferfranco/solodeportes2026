<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Evento;
use App\Models\User;

new class extends Component
{
  use Toast;

  public $evento;
  public $users, $user;

  public function mount(Evento $evento) {
    $this->evento = $evento;
    $this->users  = User::orderBy('name')->get();
    $this->user   = $evento->administradores()
      ->wherePivot('rol', 'admin')
      ->first()
      ->id ?? null;
    info($this->user);
  }

  public function updatedUser($user) {
    info("User updated: $user");

    $adm = $this->evento
      ->administradores()
      ->wherePivot('rol', 'admin')
      ->first();
    info("Current admin: " . ($adm->name ?? 'none'));

    if ($adm) {
      $this->evento->administradores()->detach($adm->id);
      info("Detached admin: " . $adm->name);
      $this->evento->administradores()->attach($user, ['rol' => 'admin']);
      info("Attached new admin: " . $this->users->find($user)->name);
    } else {
      $this->evento->administradores()->attach($user, ['rol' => 'admin']);
      info("Attached new admin: " . $this->users->find($user)->name);
    }

    $this->success('Administrador actualizado');
  }
};
?>

<x-select
  wire:model.live='user'
  :options="$users"
  option-label="name"
  option-value="id"
  class="outline-none! w-full"
  placeholder="Seleccionar administrador"
  />
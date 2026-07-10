<?php

use Livewire\Component;

new class extends Component
{
  public $user;

  public function mount() {
    $this->user = auth()->user();
  }
};
?>

<x-menu
  active-bg-color="bg-primary text-primary-content hover:text-primary-content"

  activate-by-route
  >
  <x-menu-separator />

  <x-menu-item
    title="Inicio"
    icon="lucide.home"
    link="{{ route('dashboard') }}"
    />
  <x-menu-item title="Banco" icon="lucide.piggy-bank" link="{{ route('banco') }}" />
  <livewire:menu-item-notification />
  <x-menu-item title="Tienda" icon="lucide.store" link="{{ route('tienda') }}" />

  {{-- Administradores de eventos --}}
  @if ($user && $user->administrador_eventos)
    <x-menu-separator />
    <p class="px-4 text-xs font-bold text-gray-500 uppercase">administrar Eventos</p>

    @foreach ($user->eventosAdministrados as $evento)
      <x-menu-item title="{{ $evento->nombre }}" icon="lucide.trophy" link="{{ route('evento.show', $evento) }}" />
    @endforeach

  @endif

  {{-- Administración global --}}
  @if ($user && $user->is_admin)
    <x-menu-separator />
    <p class="px-4 text-xs font-bold text-gray-500 uppercase">Admin</p>

    <x-menu-item title="Anuncios" icon="lucide.megaphone" link="{{ route('admin.anuncios.index') }}" />
    <x-menu-item title="Usuarios" icon="lucide.users" link="{{ route('admin.users.index') }}" />
    <x-menu-item title="Deportes" icon="lucide.medal" link="{{ route('admin.deportes.index') }}" />
    <x-menu-item title="Temporadas" icon="lucide.calendar" link="{{ route('admin.temporadas.index') }}" />
    <x-menu-item title="Eventos" icon="lucide.trophy" link="{{ route('admin.eventos.index') }}" />
    <x-menu-item title="Banco" icon="lucide.piggy-bank" link="{{ route('admin.banco') }}" />
  @endif
</x-menu>

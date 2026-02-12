<?php

use Livewire\Component;

new class extends Component
{
  public $notificaciones;
  public $headers;

  public function mount() {
    $this->notificaciones = auth()->user()->notifications()->get();
    $this->headers = [
      ['key' => 'id', 'label' => 'Notificación'],
    ];
  }

  public function marcarLeido($id) {
    $notificacion = auth()->user()->notifications()->where('id', $id)->first();
    if ($notificacion) {
      $notificacion->markAsRead();
      $this->notificaciones = auth()->user()->notifications()->get();
    }
  }

  public function borrar($id) {
    $notificacion = auth()->user()->notifications()->where('id', $id)->first();
    if ($notificacion) {
      $notificacion->delete();
      $this->notificaciones = auth()->user()->notifications()->get();
    }
  }

  public function marcarTodasLeidas() {
    auth()->user()->unreadNotifications->markAsRead();
    $this->notificaciones = auth()->user()->notifications()->get();
  }

  public function borrarTodas() {
    auth()->user()->notifications()->delete();
    $this->notificaciones = [];
  }
};
?>

<div>
  <x-title title="Notificaciones" />

  <div class="flex gap-2 items-center mb-4">
    <x-button
      label="Marcar todas como leídas"
      icon="fas.envelope-open"
      class="btn-primary"
      wire:click="marcarTodasLeidas"
      />
    <x-button
      label="Borrar todas"
      icon="fas.trash-can"
      class="btn-error"
      wire:click="borrarTodas"
      />
  </div>

  <x-table :headers="$headers" :rows="$notificaciones">
    @scope('cell_id', $n)
      @php
        $color = $n->data['color'] ?? 'base-content';
      @endphp
      <div class="flex gap-2 items-start {{ $n->read_at ? 'text-' . $color . '/50' : 'text-' . $color . " font-bold" }}">
        <x-icon
          :name="$n?->data['icon'] ?? 'fas.info-circle'"
          class="w-6 h-6"
          />
        <div class="{{ $n->read_at ? '' : 'font-bold' }}">
          <p>{{ $n->data['message'] }}</p>
          <p>Hace: {{ $n->created_at->diffForHumans() }}</p>
        </div>
      </div>
    @endscope

    @scope('actions', $n)
      <div class="flex gap-1 items-center">
        @if ($n->data['url'] ?? false)
          <x-button
            icon="fas.magnifying-glass"
            class="btn-ghost btn-primary btn-sm"
            link="{{ $n->data['url'] }}"
            />
        @endif
        @if ($n->read_at === null)
          <x-button
            icon="fas.envelope-open"
            class="btn-ghost btn-primary btn-sm"
            wire:click="marcarLeido('{{ $n->id }}')"
            />
        @endif
        <x-button
          icon="fas.trash-can"
          class="btn-ghost btn-error btn-sm"
          wire:click="borrar('{{ $n->id }}')"
          />
      </div>


    @endscope
  </x-table>
</div>
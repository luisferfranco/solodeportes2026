<?php

use Livewire\Component;

new class extends Component
{
  public $n;

  public function mount() {
    $this->getData();
  }

  public function getData() {
    $this->n = auth()->user()->unreadNotifications()->count();
  }
};
?>

<x-menu-item
  title="Notificaciones"
  icon="lucide.bell"
  link="{{ route('notificaciones') }}"
  badge="{{ $n }}"
  badge-classes="badge-error float-right"
  wire:poll.5000ms="getData()"
  />

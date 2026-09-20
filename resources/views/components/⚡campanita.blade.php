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

<div
  class="relative"
  wire:poll.5000ms="getData()"
  >
  <x-icon name="lucide.bell" class="text-xl" />
  @if($n > 0)
    <span class="absolute top-0 right-0 inline-block w-3 h-3 bg-red-600 rounded-full"></span>
  @endif
</div>
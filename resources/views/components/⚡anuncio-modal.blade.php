<?php

use Livewire\Component;
use App\Models\Anuncio;

new class extends Component
{
  public Anuncio $anuncio;
  public $show = true;
  public $dismissed = false;

  public function mount(Anuncio $anuncio) {
    $this->anuncio = $anuncio;
  }

  public function updatedDismissed($value) {
    if ($value) {
      $this->anuncio->usuarios()->attach(auth()->id());
    } else {
      $this->anuncio->usuarios()->detach(auth()->id());
    }
  }
};
?>

<x-modal
  wire:model="show"
  class="border-none backdrop-blur-md"
  box-class="w-full! max-w-full! md:max-w-4xl! md:w-5xl! md:border md:border-base-300 bg-base-100"
  >
  <div class="markdown">
    {!! Str::of($anuncio->cuerpo)->markdown() !!}
  </div>
  <div class="mt-6">
    <x-toggle
      wire:model.live="dismissed"
      label="No volver a mostrar este anuncio"
      />
  </div>
</x-modal>
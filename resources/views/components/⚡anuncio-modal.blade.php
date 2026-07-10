<?php

use Livewire\Component;
use App\Models\Anuncio;

new class extends Component
{
  public Anuncio $anuncio;
  public $show = true;
  public $dismissed = false;

  public function mount(Anuncio $anuncio) {
    $this->anuncio    = $anuncio;
    $this->dismissed  = $anuncio->usuarios()
      ->where('user_id', auth()->id())
      ->wherePivot('no_mostrar', true)
      ->exists();

    // Si el anuncio se ha presentado al usuario en los pasados diez minutos, no deberá presentarse, se usará la columna fecha_visto de la tabla pivote para el cálculo
    if ($anuncio->usuarios()
      ->where('user_id', auth()->id())
      ->wherePivot('fecha_visto', '>=', now()->subMinutes(10))
      ->exists()) {
      $this->show = false;
      return;
    }

    // Ahora se deberá actualizar la fecha_visto con este momento, para que no se vuelva a mostrar el anuncio en los próximos diez minutos
    $anuncio->usuarios()->syncWithoutDetaching([
      auth()->id() => ['fecha_visto' => now()]
    ]);

  }

  public function updatedDismissed($value) {
    if ($value) {
      $this->anuncio->usuarios()->updateExistingPivot(auth()->id(), ['no_mostrar' => true]);
    } else {
      $this->anuncio->usuarios()->updateExistingPivot(auth()->id(), ['no_mostrar' => false]);
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

  <div class="flex justify-between mt-5 items-center">
    <div class="text-base-content/20 text-xs">Anuncio {{ sprintf("%04d", $anuncio->id) }}</div>
    <div>
      <x-button
        wire:click="$set('show', false)"
        class="btn-primary"
        label="Cerrar"
        />
    </div>
  </div>
</x-modal>
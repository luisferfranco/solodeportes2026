<?php

use Livewire\Component;

new class extends Component
{
  public $anuncios;

  public function mount() {
    // Los anuncios que se deberán presentar deberán cumplir con lo siguiente:
    // Si existe desde, la fecha de hoy deberá ser igual o posterior
    // Si existe hasta, la fecha de hoy deberá ser igual o anterior
    // Solo deberán mostrarse los anuncios que NO tengan un registro en anuncio_users, ya que esta si existe, el usuario ha indicado que no quiere volver a ver ese anuncio
    $this->anuncios = \App\Models\Anuncio::where(function ($query) {
      $query->whereNull('desde')
        ->orWhere('desde', '<=', now());
    })
    ->where(function ($query) {
      $query->whereNull('hasta')
        ->orWhere('hasta', '>=', now());
    })
    ->whereDoesntHave('usuarios', function ($query) {
      $query->where('user_id', auth()->id());
    })
    ->get();
  }
};
?>

<div>
  @foreach ($anuncios as $anuncio)
    <livewire:anuncio-modal :anuncio="$anuncio" :key="$anuncio->id" />
  @endforeach
</div>
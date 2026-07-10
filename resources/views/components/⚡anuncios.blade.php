<?php

use Livewire\Component;

new class extends Component
{
  public $anuncios;

  public function mount() {
    // Los anuncios que se deberán presentar deberán cumplir con lo siguiente:
    // Si existe desde, la fecha de hoy deberá ser igual o posterior
    // Si existe hasta, la fecha de hoy deberá ser igual o anterior
    // Solo se deberán mostrar los anuncios donde no_mostrar sea falso
    $this->anuncios = \App\Models\Anuncio::where('estado', 'activo')
      ->where(function ($query) {
        $query->whereNull('desde')
          ->orWhere('desde', '<=', now());
      })
      ->where(function ($query) {
        $query->whereNull('hasta')
          ->orWhere('hasta', '>=', now());
      })
      ->whereDoesntHave('usuarios', function ($query) {
        $query->where('anuncio_users.user_id', auth()->id())
          ->where('anuncio_users.no_mostrar', true);
      })
      ->orderBy('id', 'desc')
      ->get();
  }
};
?>

<div>
  @foreach ($anuncios as $anuncio)
    <livewire:anuncio-modal :anuncio="$anuncio" :key="$anuncio->id" />
  @endforeach
</div>
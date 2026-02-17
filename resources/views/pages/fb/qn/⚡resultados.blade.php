<?php

use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Pronostico;
use Livewire\Component;

new class extends Component
{
  public ?Participacion $participacion;
  public $pronosticos;
  public $rd;
  public $juegos;
  public $juegosIds;
  public $headers;

  public function mount(?Participacion $participacion = null)
  {
    $this->participacion = $participacion
      ?? Participacion::where('evento_id', $evento->id)
          ->where('user_id', auth()->id())
          ->first();
    $this->rd = request()->get('rd') ?? $this->participacion->evento->temporada->ronda;

    $this->pronosticos = Pronostico::query()
      ->with('juego', 'juego.awayTeam', 'juego.homeTeam')
      ->where('participacion_id', $this->participacion->id)
      ->whereHas('juego', function ($query): void {
        $query->where('ronda', $this->rd);
      })
      ->orderBy('id')
      ->get();
  }
};
?>

<div>
  <ul>
    @foreach ($this->pronosticos as $pronostico)
      <li>
        {{ $pronostico->juego->homeTeam->nombre }} vs {{ $pronostico->juego->awayTeam->nombre }}:
        {{ $pronostico->diferencia }}
        ({{ $pronostico->juego->home_score }} - {{ $pronostico->juego->away_score }})
        {{ $pronostico->res }}
        {{ $pronostico->dif }}
      </li>
    @endforeach
  </ul>
</div>
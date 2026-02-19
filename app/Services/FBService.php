<?php

namespace App\Services;

use App\Models\Leaderboard;
use App\Models\Pronostico;
use App\Models\Temporada;

class FBService
{
  public function califica(Temporada $temporada, $ronda) {
    $juegos = $temporada
      ->juegos()
      ->where('ronda', $ronda)
      ->get();

    // Debería calificar todos los juegos para todos los pronósticos,
    // independientemente de si está en uno u otro evento.

    foreach ($juegos as $juego) {
      $dif = $juego->home_score - $juego->away_score;

      // Todas las calificaciones a cero
      $updated = Pronostico::query()
        ->where('juego_id', $juego->id)
        ->update(['res' => 0, 'dif' => 0]);

      // Las que le hayan atinado al ganador, pero no la diferencia, 2 puntos
      $updated = Pronostico::query()
        ->where('juego_id', $juego->id)
        ->whereRaw('SIGN(diferencia) = SIGN(?)', [$dif])
        ->update(['res' => 1, 'dif' => 0]);

      // Los que hayan atinado a la diferencia, 3 puntos. Debería funcionar
      // para los empates
      $updated = Pronostico::query()
        ->where('juego_id', $juego->id)
        ->where('diferencia', $dif)
        ->update(['res' => 1, 'dif' => 1]);
    }

    foreach ($temporada->eventos as $evento) {
      foreach ($evento->participaciones as $participacion) {
        $sumres = $participacion->pronosticos()
            ->whereHas('juego', function ($query) use ($ronda) {
          $query->where('ronda', $ronda);
            })
            ->sum('res');
        $sumdif = $participacion->pronosticos()
            ->whereHas('juego', function ($query) use ($ronda) {
          $query->where('ronda', $ronda);
            })
            ->sum('dif');

        Leaderboard::updateOrCreate(
          [
            'participacion_id' => $participacion->id,
            'ronda' => $ronda,
            'evento_id' => $evento->id
          ],
          [
            'aciertos' => $sumres,
            'diferencias' => $sumdif
          ]
        );
      }
    }

  }
}

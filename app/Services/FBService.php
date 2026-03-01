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

    // Resetear todas las calificaciones de la ronda
    foreach ($temporada->eventos as $evento) {
      Leaderboard::where('ronda', $ronda)
        ->where('evento_id', $evento->id)
        ->delete();
    }

    // Resetear todos los pronósticos

    // Debería calificar todos los juegos para todos los pronósticos,
    // independientemente de si está en uno u otro evento.
    foreach ($juegos as $juego) {

      Pronostico::query()
        ->where('juego_id', $juego->id)
        ->update(['res' => null, 'dif' => null]);

      if ($juego->status != 'Match Finished') {
        continue;
      }

      $dif = $juego->home_score - $juego->away_score;
      if ($temporada->deporte_id == "FA") {
        // Para el futbol americano se recalculan las diferencias
        $d = abs($dif);
        if ($d < 7) {
          $dif = $dif > 0 ? 1 : -1;;
        } else if ($d < 14) {
          $dif = $dif > 0 ? 2 : -2;
        } else if ($d < 21) {
          $dif = $dif > 0 ? 3 : -3;
        } else {
          $dif = $dif > 0 ? 4 : -4;
        }
      }

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
      $result = $participacion->pronosticos()
        ->whereHas('juego', function ($query) use ($ronda) {
          $query->where('ronda', $ronda)
          ->where('status', 'Match Finished');
        })
        ->selectRaw('SUM(res) as sumres, SUM(dif) as sumdif')
        ->first();

      Leaderboard::updateOrCreate(
        [
        'participacion_id' => $participacion->id,
        'ronda' => $ronda,
        'evento_id' => $evento->id
        ],
        [
        'aciertos' => $result->sumres ?? 0,
        'diferencias' => $result->sumdif ?? 0,
        'puntos' => ($result->sumres ?? 0) * $evento->acierto + ($result->sumdif ?? 0) * $evento->diferencia,
        ]
      );
      }
    }

  }
}

<?php

namespace App\Services;

use App\Models\Pronostico;
use App\Models\Temporada;

class FBService
{
  public function califica(Temporada $temporada, $ronda) {
    // temporadas -> eventos -> pronosticos
    // temporadas -> juegos

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

      // info("Pronósticos actualizados a 0 puntos para el juego {$juego->id}: {$updated} registros");

      // Las que le hayan atinado al ganador, pero no la diferencia, 2 puntos
      $updated = Pronostico::query()
        ->where('juego_id', $juego->id)
        ->whereRaw('SIGN(diferencia) = SIGN(?)', [$dif])
        ->update(['res' => 1, 'dif' => 0]);
      // info("Pronósticos actualizados a 2 puntos para el juego {$juego->id}: {$updated} registros");

      // Los que hayan atinado a la diferencia, 3 puntos. Debería funcionar
      // para los empates
      $updated = Pronostico::query()
        ->where('juego_id', $juego->id)
        ->where('diferencia', $dif)
        ->update(['res' => 1, 'dif' => 1]);
      // info("Pronósticos actualizados a 3 puntos para el juego {$juego->id}: {$updated} registros");
    }

  }
}

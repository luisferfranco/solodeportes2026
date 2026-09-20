<?php

namespace App\Services;

use App\Models\Leaderboard;
use App\Models\Pronostico;
use App\Models\Temporada;
use App\Models\Participacion;
use App\Models\Survivor;

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

    // Resetear todo los survivor de la ronda
    $eventos = $temporada->eventos()
      ->where('tipojuego_id', 'sr')
      ->pluck('id')
      ->toArray();
    $participaciones = Participacion::whereIn('evento_id', $eventos)
      ->pluck('id')
      ->toArray();

    // Resetear todos los pronósticos

    // Debería calificar todos los juegos para todos los pronósticos,
    // independientemente de si está en uno u otro evento.
    foreach ($juegos as $juego) {

      Pronostico::query()
        ->where('juego_id', $juego->id)
        ->update(['res' => null, 'dif' => null]);

      // Si el juego no es FT, ET o AP continuar, no calificarlo. Por ejemplo, si es NS, no calificarlo.
      if (!(in_array($juego->status, ['FT', 'ET', 'AP', 'AET', 'AOT']))) {
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

      if ($temporada->deporte_id == "FB") {
        if ($juego->status == "AET") {
          $dif = 0;
        } else {
          if ($dif > 2) {
            $dif = 2;
          }
          if ($dif < -2) {
            $dif = -2;
          }
        }
      }

      // acierto a los survivors que le hayan ido al equipo local
      if ($dif > 0) {
        Survivor::whereIn('participacion_id', $participaciones)
          ->where('equipo_id', '=', $juego->home_id)
          ->where('ronda', $ronda)
          ->update(['acierto' => 1]);
        Survivor::whereIn('participacion_id', $participaciones)
          ->where('equipo_id', '=', $juego->away_id)
          ->where('ronda', $ronda)
          ->update(['acierto' => 0]);
      }
      // acierto a los survivors que le hayan ido al equipo visitante
      if ($dif < 0) {
        Survivor::whereIn('participacion_id', $participaciones)
          ->where('equipo_id', '=', $juego->away_id)
          ->where('ronda', $ronda)
          ->update(['acierto' => 1]);
        Survivor::whereIn('participacion_id', $participaciones)
          ->where('equipo_id', '=', $juego->home_id)
          ->where('ronda', $ronda)
          ->update(['acierto' => 0]);
      }

      $survivors = Survivor::whereIn('participacion_id', $participaciones)
        ->where('ronda', $ronda)
        ->where('acierto', 1)
        ->pluck('participacion_id')
        ->toArray();
      info('caca', [$survivors]);
      Participacion::whereIn('id', $survivors)
        ->update(['survivor' => 1]);

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

    info("Actualizando Leaderboards para la {$temporada->nombre}, ronda {$ronda}");
    foreach ($temporada->eventos as $evento) {
      info("--- Evento: {$evento->nombre}");

      foreach ($evento->participaciones as $participacion) {
        info("------ Participación: {$participacion->id}");

        $result = $participacion->pronosticos()
            ->whereHas('juego', function ($query) use ($ronda) {
              $query->where('ronda', $ronda);
          })
        ->selectRaw('SUM(res) as sumres, SUM(dif) as sumdif')
        ->first();

        info("--------- Resultados: aciertos={$result->sumres}, diferencias={$result->sumdif}");

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

    // Actualizando las participaciones del survivor
    // Poner en "acierto" el valor "acierto" de la tabla Survivor para cada participación
    $survivorResults = Survivor::whereIn('participacion_id', $participaciones)
      ->where('ronda', $ronda)
      ->pluck('acierto', 'participacion_id');

    // Participacion::whereIn('id', $participaciones)
    //   ->update(['survivor' => null]);

    foreach ($survivorResults as $participacionId => $acierto) {
      if ($acierto !== null) {
        Participacion::where('id', $participacionId)
          ->update(['survivor' => $acierto]);
      }
    }

  }
}

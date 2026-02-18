<?php

namespace App\Services;

use App\Models\Juego;
use App\Models\Equipo;
use App\Models\Temporada;
use Illuminate\Support\Facades\Http;

class APIService
{
  public function cargarEquipos(Temporada $temporada) {
    $liga   = $temporada->sport_api_id;
    $apikey = env('API_KEY');
    $url    = env('API_URL') . '/v2/json/list/teams/' . $liga;

    $response = Http::withHeaders([
      'X_API_KEY' => $apikey
    ])->get($url);

    if ($response->failed()) {
      info("Temporada: {$temporada->id}", [$temporada, $temporada->deporte]);
      info("Error: $url");
      return;
    }

    $i = 1;
    $equipos = $response->json()['list'];
    foreach ($equipos as $equipo) {
      $equipoData = [
        'api_id'        => $equipo['idTeam'],
        'code'          => $equipo['strTeamShort'],
        'deporte_id'    => $temporada->deporte_id,
        'nombre'        => $equipo['strTeam'],
        'alterno'       => $equipo['strCountry'] ?? null,
        'descripcion'   => $equipo['strCountry'] ?? null,
        'claro'         => $equipo['strColour1'] ?? null,
        'oscuro'        => $equipo['strColour2'] ?? null,
        'logo'          => $equipo['strBadge'] ?? null
      ];

      info("Cargando Equipo {$i}: {$equipo['strTeam']}");
      $i++;

      \App\Models\Equipo::updateOrCreate(
        ['api_id' => $equipoData['api_id']],
        $equipoData
      );
    }
  }

  public function cargarRondas(Temporada $temporada) {
    $liga   = $temporada->sport_api_id;
    $apikey = env('API_KEY');
    $url    = env('API_URL') . "v2/json/schedule/league/{$liga}/{$temporada->temporada}";

    info("Cargando rondas para temporada {$temporada->id} - URL: $url");

    $response = Http::withHeaders([
      'X_API_KEY' => $apikey
    ])->get($url);

    if ($response->failed()) {
      info("Temporada: {$temporada->id}", [$temporada, $temporada->deporte]);
      info("Error: $url");
      return;
    }

    $schedule = $response->json()['schedule'] ?? [];

    foreach ($schedule as $game) {

      if ($temporada->fecha_inicio && $temporada->fecha_fin) {
        if ($game['dateEvent'] < $temporada->fecha_inicio || $game['dateEvent'] > $temporada->fecha_fin) {
          continue;
        }
      }

      $home_id = Equipo::where('api_id', $game['idHomeTeam'])->first();
      $away_id = Equipo::where('api_id', $game['idAwayTeam'])->first();

      $gameData = [
        'id'            => $game['idEvent'],
        'deporte_id'    => $temporada->deporte_id,
        'temporada_id'  => $temporada->id,
        'home_id'       => $home_id->id ?? null,
        'away_id'       => $away_id->id ?? null,
        'ronda'         => $game['intRound'] ?? null,
        'valido_hasta'  => $game['dateEvent'] . ' ' . ($game['strTime'] ?? '00:00:00'),
        'status'        => $game['strStatus'] ?? null,
      ];

      info("({$temporada->id}/{$gameData['ronda']}) {$home_id->nombre} vs {$away_id->nombre}");

      Juego::updateOrCreate(
        ['id' => $gameData['id']],
        $gameData
      );
    }
  }

  public function cargarMarcadores(Temporada $temporada, $ronda) {
    $liga   = $temporada->sport_api_id;
    $apikey = env('API_KEY');
    $url    = env('API_URL') . "v2/json/schedule/league/{$liga}/{$temporada->temporada}";

    // Obtener la fecha mínima y máxima de los juegos de la ronda
    $min = Juego::where('temporada_id', $temporada->id)
      ->where('ronda', $ronda)
      ->min('valido_hasta');
    $max = Juego::where('temporada_id', $temporada->id)
      ->where('ronda', $ronda)
      ->max('valido_hasta');

    $response = Http::withHeaders([
      'X_API_KEY' => $apikey
    ])->get($url);

    if ($response->failed()) {
      info("Juego: {$juego->id}", [$juego]);
      info("Error: $url");
    }

    $res = $response->json()['schedule'] ?? [];
    foreach ($res as $game) {
      if ($game['dateEvent'] . ' ' . ($game['strTime'] ?? '00:00:00') < $min || $game['dateEvent'] . ' ' . ($game['strTime'] ?? '00:00:00') > $max) {
        continue;
      }

      $juego = Juego::find($game['idEvent']);
      if (!$juego) {
        continue;
      }

      $juego->home_score  = $game['intHomeScore'];
      $juego->away_score  = $game['intAwayScore'];
      $juego->status      = $game['strStatus'];
      $juego->save();
    }

  }
}

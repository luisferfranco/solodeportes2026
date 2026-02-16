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

      info("Juego {$gameData['id']} - {$home_id->nombre} vs {$away_id->nombre} - Ronda: {$gameData['ronda']} - Valido hasta: {$gameData['valido_hasta']}");

      Juego::updateOrCreate(
        ['id' => $gameData['id']],
        $gameData
      );
    }
  }

  public function cargarMarcadores(Temporada $temporada, $ronda) {
    $liga   = $temporada->sport_api_id;
    $apikey = env('API_KEY');


    $juegos = Juego::where('temporada_id', $temporada->id)
      ->where('ronda', $ronda)
      ->get();

    foreach ($juegos as $juego) {
      $url    = env('API_URL') . "v2/json/lookup/event/{$juego->id}";
      $response = Http::withHeaders([
        'X_API_KEY' => $apikey
      ])->get($url);

      if ($response->failed()) {
        info("Juego: {$juego->id}", [$juego]);
        info("Error: $url");
        continue;
      }

      $res = $response->json()['lookup'][0] ?? [];

      $juego->home_score  = $res['intHomeScore'];
      $juego->away_score  = $res['intAwayScore'];
      $juego->status      = $res['strStatus'];
      $juego->save();
    }

  }

}

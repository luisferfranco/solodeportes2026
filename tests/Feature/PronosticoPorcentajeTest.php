<?php

use App\Models\Deporte;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Juego;
use App\Models\Participacion;
use App\Models\Pronostico;
use App\Models\Temporada;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

it('shows the forecast percentages for the home and away teams', function () {
    Schema::create('deportes', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('nombre');
        $table->string('icono')->nullable();
        $table->timestamps();
    });

    Schema::create('temporadas', function (Blueprint $table) {
        $table->id();
        $table->integer('sport_api_id')->nullable();
        $table->integer('ronda')->default(1);
        $table->integer('rondafinal')->default(1);
        $table->string('deporte_id');
        $table->string('temporada');
        $table->string('nombre');
        $table->date('fecha_inicio')->nullable();
        $table->date('fecha_fin')->nullable();
        $table->timestamps();
    });

    Schema::create('eventos', function (Blueprint $table) {
        $table->id();
        $table->string('tipojuego_id');
        $table->string('nombre');
        $table->string('slug')->nullable();
        $table->text('descripcion')->nullable();
        $table->string('estado')->nullable();
        $table->foreignId('temporada_id');
        $table->string('deporte_id');
        $table->integer('acierto')->default(1);
        $table->integer('inicia_survivor')->default(0);
        $table->integer('ronda_inicial')->default(1);
        $table->timestamp('fecha_limite')->nullable();
        $table->date('fecha_inicio_inscripcion')->nullable();
        $table->date('fecha_fin_inscripcion')->nullable();
        $table->integer('jornada_inicio')->default(1);
        $table->integer('jornada_fin')->default(1);
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('nick')->nullable();
        $table->string('avatar')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('equipos', function (Blueprint $table) {
        $table->id();
        $table->string('deporte_id');
        $table->string('nombre');
        $table->string('alterno')->nullable();
        $table->string('logo')->nullable();
        $table->timestamps();
    });

    Schema::create('juegos', function (Blueprint $table) {
        $table->id();
        $table->string('deporte_id');
        $table->foreignId('temporada_id');
        $table->foreignId('home_id');
        $table->foreignId('away_id');
        $table->integer('ronda');
        $table->integer('home_score')->nullable();
        $table->integer('away_score')->nullable();
        $table->dateTime('valido_hasta')->nullable();
        $table->string('status')->nullable();
        $table->boolean('locked')->default(false);
    });

    Schema::create('participaciones', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->foreignId('user_id');
        $table->foreignId('evento_id');
        $table->boolean('survivor')->default(false);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('pronosticos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('participacion_id');
        $table->foreignId('juego_id');
        $table->integer('diferencia')->nullable();
        $table->timestamps();
    });

    $deporte = Deporte::create([
        'id' => 'fa',
        'nombre' => 'Futbol Americano',
        'icono' => 'football',
    ]);

    $temporada = Temporada::create([
        'deporte_id' => $deporte->id,
        'ronda' => 2,
        'rondafinal' => 6,
        'temporada' => '2026',
        'nombre' => 'Temporada 2026',
        'fecha_inicio' => '2026-01-01',
        'fecha_fin' => '2026-12-31',
    ]);

    $evento = Evento::create([
        'tipojuego_id' => 'qn',
        'nombre' => 'Pronosticos Test',
        'slug' => 'pronosticos-test',
        'descripcion' => 'Evento de prueba',
        'estado' => 'activo',
        'temporada_id' => $temporada->id,
        'deporte_id' => $deporte->id,
        'acierto' => 1,
        'inicia_survivor' => 0,
        'ronda_inicial' => 1,
        'fecha_limite' => now()->addDays(30),
        'fecha_inicio_inscripcion' => now()->subDay(),
        'fecha_fin_inscripcion' => now()->addDays(10),
        'jornada_inicio' => 1,
        'jornada_fin' => 6,
    ]);

    $user = User::factory()->create();

    $participacionUno = Participacion::create([
        'user_id' => $user->id,
        'evento_id' => $evento->id,
        'nombre' => 'Primera participación',
    ]);

    $participacionDos = Participacion::create([
        'user_id' => $user->id,
        'evento_id' => $evento->id,
        'nombre' => 'Segunda participación',
    ]);

    $equipoLocal = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Local',
        'alterno' => 'LOC',
        'logo' => '/img/local.png',
    ]);

    $equipoVisitante = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Visitante',
        'alterno' => 'VIS',
        'logo' => '/img/visitante.png',
    ]);

    $juego = Juego::create([
        'deporte_id' => $deporte->id,
        'temporada_id' => $temporada->id,
        'home_id' => $equipoLocal->id,
        'away_id' => $equipoVisitante->id,
        'ronda' => 2,
        'home_score' => null,
        'away_score' => null,
        'valido_hasta' => now()->addDay(),
        'status' => 'NS',
    ]);

    Pronostico::create([
        'participacion_id' => $participacionUno->id,
        'juego_id' => $juego->id,
        'diferencia' => 2,
    ]);

    Pronostico::create([
        'participacion_id' => $participacionDos->id,
        'juego_id' => $juego->id,
        'diferencia' => -1,
    ]);

    Livewire::actingAs($user)
        ->test('pages::fa.qn.pronosticos', ['evento' => $evento, 'participacion' => $participacionUno])
        ->assertSee('50.0% a favor');
});

it('shows a no forecast message when no one has predicted a game', function () {
    Schema::create('deportes', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('nombre');
        $table->string('icono')->nullable();
        $table->timestamps();
    });

    Schema::create('temporadas', function (Blueprint $table) {
        $table->id();
        $table->integer('sport_api_id')->nullable();
        $table->integer('ronda')->default(1);
        $table->integer('rondafinal')->default(1);
        $table->string('deporte_id');
        $table->string('temporada');
        $table->string('nombre');
        $table->date('fecha_inicio')->nullable();
        $table->date('fecha_fin')->nullable();
        $table->timestamps();
    });

    Schema::create('eventos', function (Blueprint $table) {
        $table->id();
        $table->string('tipojuego_id');
        $table->string('nombre');
        $table->string('slug')->nullable();
        $table->text('descripcion')->nullable();
        $table->string('estado')->nullable();
        $table->foreignId('temporada_id');
        $table->string('deporte_id');
        $table->integer('acierto')->default(1);
        $table->integer('inicia_survivor')->default(0);
        $table->integer('ronda_inicial')->default(1);
        $table->timestamp('fecha_limite')->nullable();
        $table->date('fecha_inicio_inscripcion')->nullable();
        $table->date('fecha_fin_inscripcion')->nullable();
        $table->integer('jornada_inicio')->default(1);
        $table->integer('jornada_fin')->default(1);
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('nick')->nullable();
        $table->string('avatar')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('equipos', function (Blueprint $table) {
        $table->id();
        $table->string('deporte_id');
        $table->string('nombre');
        $table->string('alterno')->nullable();
        $table->string('logo')->nullable();
        $table->timestamps();
    });

    Schema::create('juegos', function (Blueprint $table) {
        $table->id();
        $table->string('deporte_id');
        $table->foreignId('temporada_id');
        $table->foreignId('home_id');
        $table->foreignId('away_id');
        $table->integer('ronda');
        $table->integer('home_score')->nullable();
        $table->integer('away_score')->nullable();
        $table->dateTime('valido_hasta')->nullable();
        $table->string('status')->nullable();
        $table->boolean('locked')->default(false);
    });

    Schema::create('participaciones', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->foreignId('user_id');
        $table->foreignId('evento_id');
        $table->boolean('survivor')->default(false);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('pronosticos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('participacion_id');
        $table->foreignId('juego_id');
        $table->integer('diferencia')->nullable();
        $table->timestamps();
    });

    $deporte = Deporte::create([
        'id' => 'fa',
        'nombre' => 'Futbol Americano',
        'icono' => 'football',
    ]);

    $temporada = Temporada::create([
        'deporte_id' => $deporte->id,
        'ronda' => 2,
        'rondafinal' => 6,
        'temporada' => '2026',
        'nombre' => 'Temporada 2026',
        'fecha_inicio' => '2026-01-01',
        'fecha_fin' => '2026-12-31',
    ]);

    $evento = Evento::create([
        'tipojuego_id' => 'qn',
        'nombre' => 'Sin Pronosticos',
        'slug' => 'sin-pronosticos',
        'descripcion' => 'Evento sin pronosticos',
        'estado' => 'activo',
        'temporada_id' => $temporada->id,
        'deporte_id' => $deporte->id,
        'acierto' => 1,
        'inicia_survivor' => 0,
        'ronda_inicial' => 1,
        'fecha_limite' => now()->addDays(30),
        'fecha_inicio_inscripcion' => now()->subDay(),
        'fecha_fin_inscripcion' => now()->addDays(10),
        'jornada_inicio' => 1,
        'jornada_fin' => 6,
    ]);

    $user = User::factory()->create();

    $participacion = Participacion::create([
        'user_id' => $user->id,
        'evento_id' => $evento->id,
        'nombre' => 'Sin pronósticos',
    ]);

    $equipoLocal = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Local',
        'alterno' => 'LOC',
        'logo' => '/img/local.png',
    ]);

    $equipoVisitante = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Visitante',
        'alterno' => 'VIS',
        'logo' => '/img/visitante.png',
    ]);

    Juego::create([
        'deporte_id' => $deporte->id,
        'temporada_id' => $temporada->id,
        'home_id' => $equipoLocal->id,
        'away_id' => $equipoVisitante->id,
        'ronda' => 2,
        'home_score' => null,
        'away_score' => null,
        'valido_hasta' => now()->addDay(),
        'status' => 'NS',
    ]);

    Livewire::actingAs($user)
        ->test('pages::fa.qn.pronosticos', ['evento' => $evento, 'participacion' => $participacion])
        ->assertSee('Sin pronósticos');
});

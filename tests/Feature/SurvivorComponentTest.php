<?php

use App\Models\Deporte;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Juego;
use App\Models\Participacion;
use App\Models\Survivor;
use App\Models\Temporada;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

it('saves the selected survivor team for the current round', function () {
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

    Schema::create('survivors', function (Blueprint $table) {
        $table->id();
        $table->foreignId('participacion_id');
        $table->integer('ronda');
        $table->foreignId('equipo_id');
        $table->boolean('acierto')->default(false);
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
        'tipojuego_id' => 'sr',
        'nombre' => 'Survivor Test',
        'slug' => 'survivor-test',
        'descripcion' => 'Evento de prueba',
        'estado' => 'activo',
        'temporada_id' => $temporada->id,
        'deporte_id' => $deporte->id,
        'acierto' => 1,
        'inicia_survivor' => 1,
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
        'nombre' => 'Mi boleto',
        'survivor' => 1,
    ]);

    $equipoA = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Equipo A',
        'alterno' => 'A',
        'logo' => '/img/a.png',
    ]);

    $equipoB = Equipo::create([
        'deporte_id' => $deporte->id,
        'nombre' => 'Equipo B',
        'alterno' => 'B',
        'logo' => '/img/b.png',
    ]);

    $juego = Juego::create([
        'deporte_id' => $deporte->id,
        'temporada_id' => $temporada->id,
        'home_id' => $equipoA->id,
        'away_id' => $equipoB->id,
        'ronda' => 2,
        'home_score' => null,
        'away_score' => null,
        'valido_hasta' => now()->addDay(),
        'status' => 'scheduled',
    ]);

    $this->actingAs($user)
        ->get(route('fa.sr.pronosticos', ['evento' => $evento, 'p' => $participacion->id]))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::fa.sr.pronosticos', ['evento' => $evento, 'participacion' => $participacion])
        ->call('seleccionarEquipo', $juego->id, $equipoA->id)
        ->assertSet('seleccionadoId', $equipoA->id)
        ->assertSet('estadoJugador', 'vivo');

    expect(Survivor::where('participacion_id', $participacion->id)
        ->where('ronda', 2)
        ->first()->equipo_id)->toBe($equipoA->id);
});

<?php

namespace App\Models;

use App\Enums\EventoStatus;
use App\Enums\TipoJuegoEnum;
use App\Models\Deporte;
use App\Models\Participacion;
use App\Models\Temporada;
use App\Models\TipoJuego;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
  protected $fillable = [
    'tipojuego_id', 'nombre', 'descripcion', 'temporada_id',
    'imagen', 'precio', 'deporte_id', 'aceirto', 'inicia_survivor',
    'diferencia', 'reglas', 'estado', 'ronda_inicial', 'created_at', 'updated_at', 'fecha_limite', 'fecha_inicio_inscripcion', 'fecha_fin_inscripcion'
  ];

  protected $casts = [
    'estado' => EventoStatus::class,
    'fecha_limite' => 'datetime',
    'fecha_inicio_inscripcion' => 'datetime',
    'fecha_fin_inscripcion' => 'datetime',
  ];

  public function getImagenUrlAttribute($value)
  {
    return $value ? asset('storage/' . $value) : '/img/evento-default.png';
  }

  //! RELACIONES
  public function tipoJuego() {
    return $this->belongsTo(TipoJuego::class, 'tipojuego_id');
  }

  public function temporada() {
    return $this->belongsTo(Temporada::class);
  }

  public function deporte() {
    return $this->belongsTo(Deporte::class);
  }

  public function participaciones() {
    return $this->hasMany(Participacion::class);
  }

  public function leaderboard() {
    return $this->hasMany(Leaderboard::class);
  }

  public function users() {
    return $this->belongsToMany(User::class, 'participacions')->withTimestamps();
  }

}

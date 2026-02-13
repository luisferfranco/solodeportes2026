<?php

namespace App\Models;

use App\Models\Equipo;
use App\Models\Pronostico;
use App\Models\Temporada;
use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
  public $timestamps = false;
  protected $fillable = [
    'id', 'temporada_id', 'deporte_id', 'home_id', 'away_id',
    'ronda', 'home_score', 'away_score', 'valido_hasta', 'status',
    'created_at', 'updated_at', 'youtube', 'locked'
  ];
  public $casts = [
    'valido_hasta' => 'datetime',
  ];

   //! RELACIONES
  public function temporada() {
    return $this->belongsTo(Temporada::class);
  }

  public function homeTeam() {
    return $this->belongsTo(Equipo::class, 'home_id');
  }

  public function awayTeam() {
    return $this->belongsTo(Equipo::class, 'away_id');
  }

  public function pronosticos() {
    return $this->hasMany(Pronostico::class);
  }
}

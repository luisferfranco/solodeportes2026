<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
  public $timestamps = false;
  protected $fillable = [
    'id', 'temporada_id', 'deporte_id', 'home_id', 'away_id',
    'ronda', 'home_score', 'away_score', 'valido_hasta', 'status',
    'created_at', 'updated_at', 'youtube', 'locked'
  ];

  public function temporada() {
    return $this->belongsTo(Temporada::class);
  }

  public function equipoLocal() {
    return $this->belongsTo(Equipo::class, 'home_id');
  }

  public function equipoVisitante() {
    return $this->belongsTo(Equipo::class, 'away_id');
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
  protected $fillable = [
    'code', 'deporte_id', 'api_id', 'nombre', 'alterno', 'estadio',
    'capacidad', 'localidad', 'www', 'facebook', 'twitter', 'instagram',
    'youtube', 'descripcion', 'claro', 'oscuro', 'logo', 'created_at', 'updated_at'
  ]
  ;
  public function deporte() {
    return $this->belongsTo(Deporte::class);
  }

  public function juegosLocal() {
    return $this->hasMany(Juego::class, 'home_id');
  }

  public function juegosVisitante() {
    return $this->hasMany(Juego::class, 'away_id');
  }
}

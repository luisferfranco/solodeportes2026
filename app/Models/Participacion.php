<?php

namespace App\Models;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Participacion extends Model
{
  protected $table = 'participaciones';
  protected $fillable = [
    'nombre',
    'user_id',
    'evento_id',
    // Indicación si esta vivo para los eventos de survivor
    'survivor',
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function evento() {
    return $this->belongsTo(Evento::class);
  }

  public function pronosticos() {
    return $this->hasMany(Pronostico::class, 'participacion_id');
  }

  public function leaderboard() {
    return $this->hasOne(Leaderboard::class, 'participacion_id');
  }
}

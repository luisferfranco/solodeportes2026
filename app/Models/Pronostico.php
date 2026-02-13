<?php

namespace App\Models;

use App\Models\Juego;
use Illuminate\Database\Eloquent\Model;

class Pronostico extends Model
{
  protected $fillable = [
    'participacion_id',
    'juego_id',
    'diferencia',
    'quien',
    'res',
    'dif'
  ];

   //! RELACIONES
  public function juego() {
    return $this->belongsTo(Juego::class);
  }
}

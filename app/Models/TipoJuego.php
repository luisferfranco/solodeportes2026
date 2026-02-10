<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoJuego extends Model
{
  protected $table = 'tipo_juegos';
  protected $fillable = [
    'id', 'nombre', 'descripcion', 'reglas', 'created_at', 'updated_at'
  ];
  protected $keyType = 'string';
  public $incrementing = false;

  public function eventos()
  {
    return $this->hasMany(Evento::class);
  }
}

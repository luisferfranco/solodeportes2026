<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deporte extends Model
{
  protected $fillable = [
    'id', 'apikey', 'nombre', 'descripcion', 'icono'
  ];
  protected $primaryKey = 'id';
  public $incrementing = false;
  protected $keyType = 'string';
}

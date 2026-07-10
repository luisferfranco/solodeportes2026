<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
  protected $fillable = [
    'titulo',
    'cuerpo',
    'desde',
    'hasta',
    'estado',
    'autor_id',
  ];

  public function casts(): array
  {
    return [
      'desde' => 'date',
      'hasta' => 'date',
    ];
  }

  public function autor()
  {
    return $this->belongsTo(User::class, 'autor_id');
  }
  public function usuarios()
  {
    return $this->belongsToMany(User::class, 'anuncio_users');
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitacion extends Model
{
  protected $guarded=[];
  public $table = 'invitaciones';

  protected function casts(): array
  {
    return [
      'caduca' => 'datetime',
      'accepted_at' => 'datetime',
      'rejected_at' => 'datetime',
    ];
  }

  public function evento() {
    return $this->belongsTo(Evento::class, 'evento_id');
  }
  public function invitado() {
    return $this->belongsTo(User::class, 'invitado_id');
  }
  public function invitante() {
    return $this->belongsTo(User::class, 'invitante_id');
  }

}

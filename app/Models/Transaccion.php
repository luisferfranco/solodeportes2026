<?php

namespace App\Models;

use App\Enums\EstadoTransaccion;
use App\Enums\TipoTransaccion;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
  public $table = 'transacciones';
  protected $fillable = [
    'user_id',
    // [deposito, retiro, compra, premio]
    'tipo',
    // Para indicar los premios de los eventos y la semana premiada
    'semana_premiada',
    'evento_id',
    // positivos y negativos
    'monto',
    'comprobante',
    // [pendiente, aprobada, rechazada, cancelada]
    'estado',
    'notas',

    // CLABE para la que se solicitó el retiro
    'clabe',
  ];
  protected $casts = [
    'tipo'    => TipoTransaccion::class,
    'estado'  => EstadoTransaccion::class,
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function evento()
  {
    return $this->belongsTo(Evento::class);
  }

}

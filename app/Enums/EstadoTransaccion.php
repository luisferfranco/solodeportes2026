<?php

namespace App\Enums;

enum EstadoTransaccion:string
{
  case PENDIENTE  = 'pendiente';
  case APROBADA   = 'aprobada';
  case RECHAZADA  = 'rechazada';
  case CANCELADA  = 'cancelada';

  public function label(): string
  {
    return match($this) {
      self::PENDIENTE => 'PENDIENTE',
      self::APROBADA  => 'APROBADA',
      self::RECHAZADA => 'RECHAZADA',
      self::CANCELADA => 'CANCELADA',
    };
  }

  public function color(): string
  {
    return match($this) {
      self::PENDIENTE => 'warning',
      self::APROBADA  => 'success',
      self::RECHAZADA => 'danger',
      self::CANCELADA => 'neutral',
    };
  }
}

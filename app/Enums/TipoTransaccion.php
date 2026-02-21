<?php

namespace App\Enums;

enum TipoTransaccion: string
{
  const __default = self::UNKNOWN;

  case DEPOSITO = 'deposito';
  case RETIRO   = 'retiro';
  case COMPRA   = 'compra';
  case PREMIO   = 'premio';
  case UNKNOWN  = 'unknown';

  public function label(): string
  {
    return match($this) {
      self::DEPOSITO => 'DEPÓSITO',
      self::RETIRO   => 'RETIRO',
      self::COMPRA   => 'COMPRA',
      self::PREMIO   => 'PREMIO',
      self::UNKNOWN  => 'DESCONOCIDO',
    };
  }

  public function color(): string
  {
    return match($this) {
      self::DEPOSITO => 'success',
      self::RETIRO   => 'error',
      self::COMPRA   => 'warning',
      self::PREMIO   => 'accent',
      self::UNKNOWN  => 'secondary',
    };
  }
}
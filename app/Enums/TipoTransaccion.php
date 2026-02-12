<?php

namespace App\Enums;

enum TipoTransaccion: string
{
  case DEPOSITO = 'deposito';
  case RETIRO   = 'retiro';

  public function label(): string
  {
    return match($this) {
      self::DEPOSITO => 'DEPÓSITO',
      self::RETIRO   => 'RETIRO',
    };
  }

  public function color(): string
  {
    return match($this) {
      self::DEPOSITO => 'success',
      self::RETIRO   => 'error',
    };
  }
}
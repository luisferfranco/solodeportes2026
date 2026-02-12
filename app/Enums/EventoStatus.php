<?php

namespace App\Enums;

enum EventoStatus: string
{
  case ACTIVO     = 'activo';
  case INACTIVO   = 'inactivo';
  case PENDIENTE  = 'pendiente';
  case FINALIZADO = 'finalizado';
  case ARCHIVADO  = 'archivado';

  public function label(): string
  {
    return match($this) {
      self::ACTIVO     => 'Activo',
      self::INACTIVO   => 'Inactivo',
      self::PENDIENTE  => 'Pendiente',
      self::FINALIZADO => 'Finalizado',
      self::ARCHIVADO  => 'Archivado',
    };
  }

  public function color(): string
  {
    return match($this) {
      self::ACTIVO     => 'success',
      self::INACTIVO   => 'error',
      self::PENDIENTE  => 'info',
      self::FINALIZADO => 'info',
      self::ARCHIVADO  => 'neutral',
    };
  }

  public static function options(): array
  {
    return array_map(function ($case) {
      return [
        'id'    => $case->value,
        'name'  => $case->label(),
      ];
    }, self::cases());
  }
}

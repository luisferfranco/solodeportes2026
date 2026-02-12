<?php

namespace App\Enums;

enum EventoStatus: string
{
  const __default = self::UNKNOWN;

  case ACTIVO     = 'activo';     // Recibiendo inscripciones
  case INACTIVO   = 'inactivo';   // Suspendido, no recibe inscripciones
  case ENCURSO    = 'encurso';    // Comenzado, no recibe inscripciones
  case PENDIENTE  = 'pendiente';  // Por publicarse, no recibe inscripciones
  case FINALIZADO = 'finalizado'; // Terminado, no recibe inscripciones
  case ARCHIVADO  = 'archivado';  // Archivado, no recibe inscripciones
  case UNKNOWN    = 'unknown';    // Desconocido, no recibe inscripciones

  public function label(): string
  {
    return match($this) {
      self::ACTIVO     => 'Activo',
      self::INACTIVO   => 'Inactivo',
      self::ENCURSO    => 'En Curso',
      self::PENDIENTE  => 'Proximamente',
      self::FINALIZADO => 'Finalizado',
      self::ARCHIVADO  => 'Archivado',
      self::UNKNOWN    => 'Desconocido',
    };
  }

  public function color(): string
  {
    return match($this) {
      self::ACTIVO     => 'success',
      self::INACTIVO   => 'error',
      self::ENCURSO    => 'success',
      self::PENDIENTE  => 'warning',
      self::FINALIZADO => 'info',
      self::ARCHIVADO  => 'accent',
      self::UNKNOWN    => 'secondary',
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

<?php

namespace App\Models;

use App\Models\Juego;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Pronostico extends Model
{
  use LogsActivity;

  protected $fillable = [
    'participacion_id',
    'juego_id',
    'diferencia',
    'quien',
    'res',
    'dif'
  ];

  protected $casts = [
    'diferencia' => 'integer',
    'quien' => 'integer',
    'res' => 'integer',
    'dif' => 'integer'
  ];

  public function getActivitylogOptions(): LogOptions
  {
      return LogOptions::defaults()
          ->logAll()
          ->logOnlyDirty();
  }


  //! RELACIONES
  public function juego() {
    return $this->belongsTo(Juego::class);
  }
  public function participacion() {
    return $this->belongsTo(Participacion::class);
  }
}

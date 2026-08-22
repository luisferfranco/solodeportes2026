<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded([])]
class Equipo extends Model
{
    public function deporte()
    {
        return $this->belongsTo(Deporte::class);
    }

    public function juegosLocal()
    {
        return $this->hasMany(Juego::class, 'home_id');
    }

    public function juegosVisitante()
    {
        return $this->hasMany(Juego::class, 'away_id');
    }
}

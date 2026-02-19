<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $fillable = [
        'participacion_id',
        'evento_id',
        'ronda',
        'aciertos',
        'diferencias',
    ];

    public function participacion()
    {
        return $this->belongsTo(Participacion::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}

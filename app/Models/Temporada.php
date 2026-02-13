<?php

namespace App\Models;

use App\Models\Deporte;
use App\Models\Juego;
use Illuminate\Database\Eloquent\Model;

class Temporada extends Model
{
    protected $table = 'temporadas';
    public $timestamps = false;
    protected $fillable = [
        'sport_api_id',
        'ronda',
        'ronda_final',
        'deporte_id',
        'temporada',
        'nombre',
    ];

    public function deporte()
    {
        return $this->belongsTo(Deporte::class);
    }

    public function juegos()
    {
        return $this->hasMany(Juego::class);
    }
}

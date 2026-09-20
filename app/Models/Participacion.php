<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Guarded;

#[Guarded('survivor')]
class Participacion extends Model
{
    use SoftDeletes;

    protected $table = 'participaciones';

    public function casts(): array {
      return [
        'survivor' => 'boolean',
      ];
    }

    // protected $fillable = [
    //     'nombre',
    //     'user_id',
    //     'evento_id',
    //     // Indicación si esta vivo para los eventos de survivor
    //     'survivor',
    // ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function evento() {
        return $this->belongsTo(Evento::class);
    }

    public function pronosticos() {
        return $this->hasMany(Pronostico::class, 'participacion_id');
    }

    public function survivors() {
        return $this->hasMany(Survivor::class, 'participacion_id');
    }

    public function leaderboard()
    {
        return $this->hasOne(Leaderboard::class, 'participacion_id');
    }
}

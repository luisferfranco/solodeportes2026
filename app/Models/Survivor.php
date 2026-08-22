<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Guarded;

#[Guarded([])]
class Survivor extends Model
{
    protected $fillable = [
        'participacion_id',
        'ronda',
        'equipo_id',
        'acierto',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function participacion(): BelongsTo
    {
        return $this->belongsTo(Participacion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}

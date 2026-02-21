<?php

namespace App\Services;

use App\Models\Participacion;
use App\Models\Transaccion;
use App\Notifications\PremioNotification;

class BancoService
{
    public function premio(Participacion $participacion, $ronda, $monto)
    {
        // Depósito
        $transaccion = Transaccion::create([
            'user_id'         => $participacion->user_id,
            'semana_premiada' => $ronda,
            'tipo'            => 'premio',
            'evento_id'       => $participacion->evento_id,
            'monto'           => $monto,
            'estado'          => 'aprobada',
            'notas'           => "Premio por ronda $ronda en evento {$participacion->evento->nombre} ({$participacion->evento_id})",
        ]);

        // Notificación
        // pass the numeric amount rather than the transaction itself
        $participacion->user->notify(new PremioNotification(
            $participacion,
            $monto,
            $ronda
        ));
    }
}

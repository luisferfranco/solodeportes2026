<?php

namespace App\Notifications;

use App\Models\Participacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PremioNotification extends Notification
{
    use Queueable;

    protected Participacion $participacion;

    protected float $monto;

    protected $semana;

    public function __construct(Participacion $participacion, float $monto, $semana)
    {
        $this->participacion = $participacion;
        $this->monto = $monto;
        $this->semana = $semana;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = '¡Felicidades! Has ganado un premio de '.sprintf('%.2f', $this->monto).' por tu participación de la semana '.$this->semana.' en el evento '.$this->participacion->evento->nombre." ({$this->participacion->evento->id}). El monto ha sido depositado en tu cuenta.";

        return [
            'user_id' => $this->participacion->user->id,
            'message' => $message,
            'icon' => 'fas.thumbs-up',
            'color' => 'success',
        ];
    }
}

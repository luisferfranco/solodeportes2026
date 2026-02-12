<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Transaccion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BankProcessedNotification extends Notification
{
    use Queueable;

    public $user, $transaccion;

    public function __construct(User $user, Transaccion $transaccion)
    {
        $this->user = $user;
        $this->transaccion = $transaccion;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = "Tu solicitud de " . $this->transaccion->tipo->label() . " de " . sprintf('%.2f', $this->transaccion->monto) . " ha sido " . $this->transaccion->estado->label();
        $url = route('banco.show', $this->transaccion);

        return [
            'user_id' => $this->user->id,
            'message' => $message,
            'url'     => $url,
            'icon'    => 'fas.piggy-bank',
            'color'   => $this->transaccion->estado === \App\Enums\EstadoTransaccion::APROBADA ? 'success' : 'error',
        ];
    }
}

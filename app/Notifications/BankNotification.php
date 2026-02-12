<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Transaccion;
use Illuminate\Bus\Queueable;
use App\Enums\TipoTransaccion;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BankNotification extends Notification
{
    use Queueable;

    public $user, $transaccion;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Transaccion $transaccion)
    {
        $this->user = $user;
        $this->transaccion = $transaccion;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->transaccion->tipo === TipoTransaccion::DEPOSITO) {
            $message = $this->user->name . ' ha realizado un depósito de ' . $this->transaccion->monto;
        } else {
            $message = $this->user->name . ' ha solicitado un retiro de ' . $this->transaccion->monto;
        }
        $url = route('banco.show', $this->transaccion);

        return [
            'user_id' => $this->user->id,
            'message' => $message,
            'url'     => $url,
            'icon'    => 'fas.piggy-bank',
        ];
    }
}

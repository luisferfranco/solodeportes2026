<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Evento;
use App\Models\Invitacion;

class NotificacionInvitacionAEvento extends Notification
{
  use Queueable;

  public Invitacion $invitacion;

  public function __construct(Invitacion $invitacion) {
    $this->invitacion = $invitacion;
  }

  public function via(object $notifiable): array {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage {

    $url = url('/evento/aceptar_invitacion/' . $this->invitacion->codigo);

    return (new MailMessage)
      ->subject($this->invitacion->invitado->name . ', te han invitado a participar en el evento "' . $this->invitacion->evento->nombre . '".')
      ->from('admin@solodeportes.mx', 'Administración de SOloDeportes.mx')
      ->greeting($this->invitacion->invitado->name . ', has sido invitado a ' . $this->invitacion->evento->nombre)
      ->line('Para confirmar la participación haz click en el siguiente botón.')
      ->action('Aceptar invitación', $url)
      ->line('¡Gracias por usar nuestra aplicación!')
      ->line('Si no deseas participar, puedes ignorar este mensaje. La invitación expirará el ' . $this->invitacion->caduca->format('d/m/Y H:i') . '.')
      ->line('Este es un correo automático, Por favor no lo contestes. NO hay nadie revisando esta cuenta.');
  }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NotificacionRecuperacion extends Notification
{
  use Queueable;

  public User $user;

  public function __construct(User $user)
  {
    $this->user = $user;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->greeting('SoloDeportes.mx - Solicitud de Recuperación de Password')
      ->attach(public_path('img/solodeportes.png'), [
        'as' => 'logo.png',
        'mime' => 'image/png',
      ])
      ->from("admin@solodeportes.mx", "Administración de SoloDeportes.mx")
      ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
      ->action('Restablecer Contraseña', url('/reiniciar-password/' . $this->user->password_reset_token))
      ->line('Si no solicitaste este cambio, puedes ignorar este mensaje.')
      ->line('Este enlace de restablecimiento de contraseña expirará en 60 minutos. (' . $this->user->password_reset_expires_at->format('H:i:s') . ')')
      ->line('Por favor no contestes este correo, ya que es un mensaje automático.');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      //
    ];
  }
}

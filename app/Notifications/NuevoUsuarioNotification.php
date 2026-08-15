<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NuevoUsuarioNotification extends Notification
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
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->greeting('Se registró un nuevo usuario en el sistema')
      ->line('Se ha registrado un nuevo usuario: ' . $this->user->name)
      ->action('Ver usuario', url('/users/' . $this->user->id))
      ->line('Gracias por usar nuestra aplicación.');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toDatabase(object $notifiable): array
  {
    $message = 'Se ha registrado un nuevo usuario: ' . $this->user->name;
    $url = url('/users/' . $this->user->id);

    return [
      'user_id' => $this->user->id,
      'message' => $message,
      'url'     => $url,
      'icon'    => 'fas.user-plus',
    ];
  }
}

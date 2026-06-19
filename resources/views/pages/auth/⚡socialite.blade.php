<?php

use Livewire\Component;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Mary\Traits\Toast;

new class extends Component
{
  use Toast;

  public function mount() {
    if (auth()->check()) {
      return $this->redirect(route('dashboard'));
    }

    $recUser = Socialite::driver('google')->user();
    // id, name, email, avatar, token

    // Si el usuario no existe, crearlo
    $user = User::updateOrCreate(
      ['email'        => $recUser->email],
      [
        'name'        => $recUser->name,
        'password'    => bcrypt(uniqid()),
        'avatar'      => $recUser->avatar,
        'external_id' => $recUser->id,
      ]
    );

    auth()->login($user, true);
    $this->success(
      title: '¡Bienvenido de nuevo, ' . $user->name . '!',
      description: 'Has iniciado sesión con Google exitosamente.',
      timeout: 5000,
      icon: 'fas.thumbs-up',
      redirectTo: route('dashboard'),
    );
  }
};
?>

<div>
    {{-- Simplicity is the consequence of refined emotions. - Jean D'Alembert --}}
</div>
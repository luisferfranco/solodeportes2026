<?php

use Livewire\Component;
use App\Models\Equipo;
use Mary\Traits\Toast;
use App\Models\Participacion;
use App\Models\Survivor;
use Livewire\Attributes\On;

new class extends Component
{
  use Toast;

  public Equipo $equipo;
  public $usados;
  public $participacion;
  public $ronda;
  public $estado;

  public function mount(Equipo $equipo, $usados, $participacion, $ronda) {
    $this->equipo = $equipo;
    $this->usados = $usados;
    $this->participacion = $participacion;
    $this->ronda = $ronda;

    if ($this->isUsed()) {
      $this->estado = 'usado';
    } else if ($this->isSelected()) {
      $this->estado = 'seleccionado';
    } else {
      $this->estado = 'disponible';
    }
  }

  public function isUsed(): bool
  {
    return $this->usados->contains(function ($survivor) {
      return $survivor->equipo_id === $this->equipo->id
        || ($survivor->equipo && $survivor->equipo->id === $this->equipo->id);
    });
  }

  public function isSelected(): bool {
    return Survivor::where('participacion_id', $this->participacion->id)
      ->where('ronda', $this->ronda)
      ->where('equipo_id', $this->equipo->id)
      ->exists();
  }

  public function selectTeam(Equipo $equipo): void
  {
    // Verificar que pueda pronosticar
    $valido = $this->participacion->evento->temporada->juegos()
      ->where('ronda', $this->ronda)
      ->orderBy('valido_hasta')
      ->first()
      ->value('valido_hasta');
    if (now() > $valido) {
      $this->error(
        title: 'Selección no válida',
        description: 'El tiempo para hacer la selección en esta ronda del survivor ha expirado',
        icon: 'fas.circle-exclamation',
        timeout: 3000,
      );

      return;
    }

    // Verificar que no sea un equipo previamente usado
    if ($this->isUsed()) {
      $this->error(
        title: 'Equipo ya usado',
        description: 'Este equipo ya fue seleccionado en rondas anteriores.',
        icon: 'fas.circle-exclamation',
        timeout: 3000,
      );

      return;
    }

    // Cargar la selección del equipo
    Survivor::updateOrCreate([
      'participacion_id' => $this->participacion->id,
      'ronda' => $this->ronda,
    ], [
      'equipo_id' => $this->equipo->id,
    ]);
    $this->estado = "seleccionado";
    $this->dispatch('team-selected');
  }

  #[On('team-selected')]
  public function handleTeamSelected(): void
  {
    $this->mount($this->equipo, $this->usados, $this->participacion, $this->ronda);
  }
}
?>

<button
  type="button"
  wire:click="selectTeam({{ $equipo->id }})"
  class="btn flex gap-2 w-full min-h-14 justify-start {{ match($estado) {
    'usado'         => 'btn-error opacity-50 cursor-not-allowed',
    'seleccionado'  => 'btn-accent',
    default         => ''
  } }}"
  >
  <img src="{{ $equipo->logo }}" class="w-12 h-12">
  <x-label value="{{ $equipo->nombre }}" />
</button>
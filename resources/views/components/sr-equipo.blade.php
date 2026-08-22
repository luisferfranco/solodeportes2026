@props([
    'equipo' => null,
    'seleccionado' => null,
    'estado' => null,
    'disabled' => false,
  ])

@php
  $baseClass = $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-base-300';
  $estadoClass = match ($estado) {
      'success' => 'bg-success/50',
      'error' => 'bg-error/50',
      default => 'bg-base-100',
  };

  $equipoId = $equipo?->id ?? null;
  $seleccionadoId = $seleccionado instanceof \App\Models\Equipo
      ? $seleccionado->id
      : $seleccionado;
  $isSelected = $equipoId !== null && $seleccionadoId !== null && (int) $equipoId === (int) $seleccionadoId;
  $cardClass = $estado ? $estadoClass : ($isSelected ? 'bg-info' : 'bg-base-100');
@endphp

<div
  {{ $attributes }}
  class="flex rounded-md border-base-300 gap-2 p-4 items-center transition-colors {{ $cardClass }} {{ $baseClass }}">
  <img
    src="{{ $equipo->logo }}"
    class="w-8 h-8 object-contain"
    />
  <div>{{ $equipo->nombre }}</div>
</div>

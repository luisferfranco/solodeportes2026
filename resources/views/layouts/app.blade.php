<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-100">

  {{-- NAVBAR mobile only --}}
  <x-nav sticky class="lg:hidden">
    <x-slot:brand>
      <img src="/img/solodeplogo.png" alt="Logo" class="w-12 h-12 rounded-full me-2">
    </x-slot:brand>
    <x-slot:actions>
      <label for="main-drawer" class="lg:hidden me-3">
      <x-icon name="o-bars-3" class="cursor-pointer" />
      </label>
    </x-slot:actions>
  </x-nav>

  <x-main>
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit border-r border-gray-400 dark:border-gray-600">

      <div class="flex w-full justify-center">
        <img src="/img/solodeplogo.png" alt="Logo" class="w-32 h-32 rounded-full">
      </div>

      {{-- MENU --}}
      <x-menu activate-by-route>

        @if($user = auth()->user())
          <x-list-item
            :item="$user"
            value="name"
            avatar="avatar"
            class="rounded-none!"
            >
            <x-slot:sub-value>
              <p>Saldo: <span class="font-bold font-mono">{{ Number::format($user->saldo,2) }}</span></p>
              <div class="flex gap-1 items-center">
                <x-button icon="lucide.power" class="btn-circle btn-ghost btn-xs" no-wire-navigate link="/logout" />
                <x-button icon="lucide.settings" class="btn-circle btn-ghost btn-xs" link="/profile" />
                <x-theme-toggle darkTheme="darkqn" />
              </div>
            </x-slot:sub-value>
          </x-list-item>
        @endif

        <x-menu-item title="Inicio" icon="lucide.home" link="{{ route('dashboard') }}" />
        <x-menu-item title="Banco" icon="lucide.piggy-bank" link="{{ route('banco') }}" />
        <livewire:menu-item-notification />
        <x-menu-item title="Tienda" icon="lucide.store" link="{{ route('tienda') }}" />

        <x-menu-separator />

        <x-menu-sub title="Admin" icon="lucide.shield-user">
          <x-menu-item title="Usuarios" icon="lucide.users" link="{{ route('admin.users.index') }}" />
          <x-menu-item title="Deportes" icon="lucide.medal" link="{{ route('admin.deportes.index') }}" />
          <x-menu-item title="Temporadas" icon="lucide.calendar" link="{{ route('admin.temporadas.index') }}" />
          <x-menu-item title="Eventos" icon="lucide.trophy" link="{{ route('admin.eventos.index') }}" />
        </x-menu-sub>
      </x-menu>
    </x-slot:sidebar>

    <x-slot:content>
      {{ $slot }}
    </x-slot:content>
  </x-main>

  <x-toast />
</body>
</html>

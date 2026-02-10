<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

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
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-red-100 lg:bg-inherit border-r shadow-xl">

      <div class="flex w-full justify-center">
      <img src="/img/solodeplogo.png" alt="Logo" class="w-32 h-32 rounded-full">
      </div>

      {{-- MENU --}}
      <x-menu activate-by-route>
      {{-- User --}}
      @if($user = auth()->user())
        <x-list-item
          :item="$user"
          value="name"
          avatar="avatar"
          >
          <x-slot:sub-value>
            <p>Saldo: 5,000</p>
            <div class="flex gap-1 items-center">
              <x-button icon="s-power" class="btn-circle btn-ghost btn-xs" no-wire-navigate link="/logout" />
              <x-button icon="s-cog-6-tooth" class="btn-circle btn-ghost btn-xs" link="/profile" />
              <x-theme-toggle />
            </div>
          </x-slot:sub-value>
        </x-list-item>
      @endif

      <x-menu-item title="Hello" icon="o-sparkles" link="/" />

      <x-menu-separator />

      <x-menu-sub title="Admin" icon="o-cog-6-tooth">
        <x-menu-item title="Wifi" icon="o-wifi" link="####" />
        <x-menu-item title="Archives" icon="o-archive-box" link="####" />
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

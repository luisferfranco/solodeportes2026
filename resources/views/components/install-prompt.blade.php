<div
  x-data="installPrompt()"
  x-init="checkPrompt()"
  x-show="showBanner"
  x-cloak
  x-transition
  class="fixed bottom-0 left-0 right-0 z-50 p-4 pb-6 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl rounded-t-2xl md:max-w-md md:mx-auto"
>
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <!-- Usa aquí tu logo o favicon -->
      <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
        <img src="/icon-192x192.png" alt="Logo" class="w-full h-full object-cover">
      </div>
      <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Instalar Aplicación</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="isIOS ? 'Toca Compartir y Agregar a inicio' : 'Añade la app a tu pantalla'"></p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button @click="showBanner = false" class="p-2 text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
      <button
        x-show="!isIOS"
        @click="installApp()"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        Instalar
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('installPrompt', () => ({
    showBanner: false,
    deferredPrompt: null,
    isIOS: false,

    checkPrompt() {
      // Detectar si ya está en modo "standalone" (ya instalada)
      const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
      if (isStandalone) return;

      // Detectar iOS
      const userAgent = window.navigator.userAgent.toLowerCase();
      this.isIOS = /iphone|ipad|ipod/.test(userAgent);

      // Si es iOS, mostramos el banner explicativo después de un breve retraso
      if (this.isIOS) {
        setTimeout(() => { this.showBanner = true; }, 2000);
        return;
      }

      // Para Android/Chrome: Escuchar el evento nativo
      window.addEventListener('beforeinstallprompt', (e) => {
        // Prevenir que Chrome muestre su mini-barra por defecto
        e.preventDefault();
        // Guardar el evento para dispararlo cuando el usuario haga clic
        this.deferredPrompt = e;
        // Mostrar nuestro componente Tailwind
        this.showBanner = true;
      });
    },

    async installApp() {
      if (!this.deferredPrompt) return;

      // Disparar el prompt nativo
      this.deferredPrompt.prompt();

      // Esperar la respuesta del usuario
      const { outcome } = await this.deferredPrompt.userChoice;

      if (outcome === 'accepted') {
        this.showBanner = false;
      }

      // Limpiar el evento
      this.deferredPrompt = null;
    }
  }));
});
</script>
<script setup lang="ts">
  /**
   * Layout principal da aplicacao com sidebar compacta de icones.
   *
   * Sidebar estreita (w-16) com apenas icones SVG centralizados.
   * O logo do FlowERP substitui o texto, e cada item de navegacao
   * exibe tooltip via atributo title para acessibilidade.
   */
  import { useAuthStore } from '@/stores/auth';
  import { useRouter } from 'vue-router';
  import { computed, type Component } from 'vue';
  import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Users,
    Sun,
    Moon,
    LogOut,
  } from 'lucide-vue-next';
  import { isDark, toggleTheme } from '@/composables/useTheme';

  const auth = useAuthStore();
  const router = useRouter();

  interface INavItem {
    label: string;
    to: string;
    icon: Component;
  }

  const navItems = computed<INavItem[]>(() => {
    const items: INavItem[] = [
      { label: 'Dashboard', to: '/', icon: LayoutDashboard },
      { label: 'Produtos', to: '/products', icon: Package },
    ];

    if (auth.canSell) {
      items.push({ label: 'Vendas', to: '/sales', icon: ShoppingCart });
    }

    items.push({ label: 'Clientes', to: '/customers', icon: Users });

    return items;
  });

  function handleLogout() {
    auth.logout();
    router.push('/login');
  }
</script>

<template>
  <div class="h-screen bg-surface flex overflow-hidden relative">
    <!-- Mesh Gradient: fundo esmeralda esfumaçado apenas no modo Escuro -->
    <div v-show="isDark" class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
      <div
        class="absolute -top-20 -left-20 w-96 h-96 rounded-full bg-primary/15 blur-[120px]"
      ></div>
      <div
        class="absolute bottom-[-10%] right-[-5%] w-[35rem] h-[35rem] rounded-full bg-primary/10 blur-[150px]"
      ></div>
      <div
        class="absolute top-[40%] left-[30%] w-[25rem] h-[25rem] rounded-full bg-primary/10 blur-[130px]"
      ></div>
    </div>

    <aside
      class="w-16 bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[4px_0_24px_-8px_rgba(0,0,0,0.1)] flex flex-col items-center py-4 shrink-0 relative z-10"
    >
      <!-- Reflexo suave de luz na borda direita para reforçar o vidro -->
      <div
        class="absolute top-0 bottom-0 right-0 w-px bg-gradient-to-b from-transparent via-[var(--color-glass-shine)] to-transparent"
      ></div>

      <!-- Logo (favicon) -->
      <RouterLink to="/" class="mb-6 relative z-10" title="FlowERP">
        <img src="/favicon.svg" alt="FlowERP" class="w-8 h-8" />
      </RouterLink>

      <!-- Navegacao -->
      <nav class="flex-1 flex flex-col items-center gap-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="w-10 h-10 flex items-center justify-center rounded-input hover:bg-surface-elevated transition-colors"
          exact-active-class="bg-surface-elevated text-primary-text"
          :title="item.label"
        >
          <component :is="item.icon" :size="20" />
        </RouterLink>
      </nav>

      <!-- Acoes do rodape -->
      <div class="flex flex-col items-center gap-2">
        <button
          class="w-10 h-10 flex items-center justify-center rounded-input hover:bg-surface-elevated transition-colors cursor-pointer"
          :title="isDark ? 'Tema claro' : 'Tema escuro'"
          @click="toggleTheme()"
        >
          <Sun v-if="isDark" :size="20" class="text-primary-text" />
          <Moon v-else :size="20" class="text-secondary" />
        </button>

        <button
          class="w-10 h-10 flex items-center justify-center rounded-input hover:bg-error-bg text-error transition-colors cursor-pointer"
          title="Sair"
          @click="handleLogout"
        >
          <LogOut :size="20" />
        </button>
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto relative z-10">
      <RouterView />
    </main>
  </div>
</template>

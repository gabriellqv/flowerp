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
  <div class="h-screen bg-surface text-primary flex overflow-hidden">
    <aside
      class="w-16 bg-surface-secondary border-r border-border flex flex-col items-center py-4 shrink-0"
    >
      <!-- Logo (favicon) -->
      <RouterLink to="/" class="mb-6" title="FlowERP">
        <img src="/favicon.svg" alt="FlowERP" class="w-8 h-8" />
      </RouterLink>

      <!-- Navegacao -->
      <nav class="flex-1 flex flex-col items-center gap-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="w-10 h-10 flex items-center justify-center rounded-input hover:bg-surface-elevated transition-colors"
          active-class="bg-surface-elevated text-primary-text"
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

    <main class="flex-1 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>

<script setup lang="ts">
  /**
   * Layout principal da aplicação com sidebar compacta de ícones.
   *
   * Sidebar estreita (w-16) com apenas ícones SVG centralizados.
   * O logo do FlowERP substitui o texto, e cada item de navegação
   * exibe tooltip via atributo title para acessibilidade.
   * Inclui botão "Voltar ao topo" com glassmorphism que aparece
   * ao rolar a página para baixo.
   */
  import { useAuthStore } from '@/stores/auth';
  import { useRouter } from 'vue-router';
  import { ref, computed, type Component } from 'vue';
  import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Users,
    Sun,
    Moon,
    LogOut,
    ArrowUp,
    Tag,
    ClipboardList,
  } from 'lucide-vue-next';
  import { isDark, toggleTheme } from '@/composables/useTheme';

  const auth = useAuthStore();
  const router = useRouter();

  const mainRef = ref<HTMLElement | null>(null);
  const showScrollTop = ref(false);

  function onScroll() {
    if (mainRef.value) {
      showScrollTop.value = mainRef.value.scrollTop > 300;
    }
  }

  function scrollToTop() {
    mainRef.value?.scrollTo({ top: 0, behavior: 'smooth' });
  }

  interface INavItem {
    label: string;
    to: string;
    icon: Component;
  }

  const navItems = computed<INavItem[]>(() => {
    const items: INavItem[] = [{ label: 'Dashboard', to: '/', icon: LayoutDashboard }];

    if (auth.canSell) {
      items.push({ label: 'Vendas', to: '/sales', icon: ShoppingCart });
    }

    items.push(
      { label: 'Produtos', to: '/products', icon: Package },
      { label: 'Clientes', to: '/customers', icon: Users },
      { label: 'Categorias', to: '/categories', icon: Tag },
      { label: 'Atividades', to: '/activities', icon: ClipboardList },
    );

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

      <!-- Navegação -->
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

      <!-- Ações do rodapé -->
      <div class="flex flex-col items-center gap-2">
        <RouterLink
          to="/profile"
          class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center text-xs font-bold text-primary-text hover:bg-primary/25 transition-colors"
          title="Meu Perfil"
        >
          {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
        </RouterLink>

        <button
          class="w-10 h-10 flex items-center justify-center rounded-input hover:bg-surface-elevated transition-colors cursor-pointer"
          :title="isDark ? 'Tema claro' : 'Tema escuro'"
          @click="toggleTheme()"
        >
          <Sun v-if="isDark" :size="20" class="text-secondary" />
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

    <main ref="mainRef" class="flex-1 overflow-y-auto relative z-10 pb-20" @scroll="onScroll">
      <RouterView />
    </main>

    <!-- Scroll to Top Button (Glassmorphism) -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-8"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-8"
    >
      <button
        v-show="showScrollTop"
        class="fixed bottom-6 right-6 w-10 h-10 flex items-center justify-center rounded-full bg-[var(--color-glass-bg)] backdrop-blur-md border border-zinc-400 dark:border-[var(--color-glass-border)] text-primary hover:border-primary dark:hover:border-primary shadow-[0_4px_14px_rgba(0,0,0,0.25)] dark:shadow-[0_4px_14px_rgba(0,0,0,0.5)] hover:shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all z-50 group cursor-pointer"
        title="Voltar ao topo"
        @click="scrollToTop"
      >
        <ArrowUp :size="20" class="relative z-10 group-hover:-translate-y-1 transition-transform" />
      </button>
    </transition>
  </div>
</template>

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
  import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
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
    Menu,
    X,
  } from 'lucide-vue-next';
  import { isDark, toggleTheme } from '@/composables/useTheme';

  const auth = useAuthStore();
  const router = useRouter();

  const mainRef = ref<HTMLElement | null>(null);
  const showScrollTop = ref(false);
  const mobileMenuOpen = ref(false);

  function handleResize() {
    if (window.innerWidth >= 768 && mobileMenuOpen.value) {
      mobileMenuOpen.value = false;
    }
  }

  onMounted(() => {
    window.addEventListener('resize', handleResize);
  });

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
  });

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
  <div class="h-screen bg-surface flex flex-col md:flex-row overflow-hidden relative">
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
      class="bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] md:shadow-[4px_0_24px_-8px_rgba(0,0,0,0.1)] flex md:flex-col items-center py-2 md:py-4 px-4 md:px-0 shrink-0 relative z-50 w-full h-[72px] md:w-16 md:h-full justify-between md:justify-start"
    >
      <!-- Reflexo suave de luz na borda -->
      <div
        class="hidden md:block absolute top-0 bottom-0 right-0 w-px bg-gradient-to-b from-transparent via-[var(--color-glass-shine)] to-transparent"
      ></div>
      <div
        class="md:hidden absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
      ></div>

      <!-- Header Esquerda (Mobile: Hamburger + Logo, Desktop: Logo) -->
      <div class="flex items-center gap-3 md:mb-6 shrink-0">
        <button
          class="md:hidden text-primary-text cursor-pointer hover:bg-surface-elevated p-1.5 rounded-input transition-colors"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <Menu v-if="!mobileMenuOpen" :size="24" />
          <X v-else :size="24" />
        </button>

        <RouterLink
          to="/"
          class="relative z-10 flex items-center"
          title="FlowERP"
          @click="mobileMenuOpen = false"
        >
          <img src="/favicon.png" alt="FlowERP" class="w-6 h-6" />
        </RouterLink>
      </div>

      <!-- Navegação principal (Dropdown no Mobile) -->
      <nav
        :class="[
          'md:flex md:flex-col md:static md:w-auto md:bg-transparent md:border-none md:p-0 md:shadow-none md:h-auto items-center gap-1.5 md:gap-1',
          'absolute top-[72px] left-0 w-full h-[calc(100vh-72px)] bg-surface p-4 flex-col z-40',
          mobileMenuOpen ? 'flex' : 'hidden',
        ]"
      >
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="shrink-0 w-full md:w-10 h-14 md:h-10 flex items-center justify-start md:justify-center px-4 md:px-0 rounded-input hover:bg-surface-elevated transition-colors gap-4 md:gap-0"
          exact-active-class="bg-surface-elevated text-primary-text"
          :title="item.label"
          @click="mobileMenuOpen = false"
        >
          <component :is="item.icon" :size="22" class="md:w-5 md:h-5" />
          <span class="md:hidden font-medium text-base">{{ item.label }}</span>
        </RouterLink>
      </nav>

      <!-- Ações do rodapé -->
      <div
        class="flex md:flex-col items-center gap-2 ml-auto md:ml-0 md:mt-auto md:border-t border-border/50 md:pt-4 shrink-0"
      >
        <RouterLink
          to="/profile"
          class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-primary/15 flex items-center justify-center text-xs font-bold text-primary-text hover:bg-primary/25 transition-colors shrink-0"
          title="Meu Perfil"
        >
          {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
        </RouterLink>

        <button
          class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-input hover:bg-surface-elevated transition-colors cursor-pointer shrink-0"
          :title="isDark ? 'Tema claro' : 'Tema escuro'"
          @click="toggleTheme()"
        >
          <Sun v-if="isDark" :size="18" class="text-secondary md:w-5 md:h-5" />
          <Moon v-else :size="18" class="text-secondary md:w-5 md:h-5" />
        </button>

        <button
          class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-input hover:bg-error-bg text-error transition-colors cursor-pointer shrink-0"
          title="Sair"
          @click="handleLogout"
        >
          <LogOut :size="18" class="md:w-5 md:h-5" />
        </button>
      </div>
    </aside>

    <main
      ref="mainRef"
      :class="[
        'flex-1 relative z-10 pb-6 md:pb-20',
        mobileMenuOpen ? 'overflow-hidden' : 'overflow-y-auto',
      ]"
      @scroll="onScroll"
    >
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
        v-show="showScrollTop && !mobileMenuOpen"
        class="fixed bottom-6 right-6 w-10 h-10 flex items-center justify-center rounded-full bg-[var(--color-glass-bg)] backdrop-blur-md border border-zinc-400 dark:border-[var(--color-glass-border)] text-primary hover:border-primary dark:hover:border-primary shadow-[0_4px_14px_rgba(0,0,0,0.25)] dark:shadow-[0_4px_14px_rgba(0,0,0,0.5)] hover:shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all z-50 group cursor-pointer"
        title="Voltar ao topo"
        @click="scrollToTop"
      >
        <ArrowUp :size="20" class="relative z-10 group-hover:-translate-y-1 transition-transform" />
      </button>
    </transition>
  </div>
</template>

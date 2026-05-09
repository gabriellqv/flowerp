<script setup lang="ts">
  /**
   * Layout principal da aplicacao com sidebar de navegacao.
   *
   * Agrupa o header lateral, navegacao contextual por perfil
   * e o botao de logout em um shell responsivo com Tailwind.
   */
  import { useAuthStore } from '@/stores/auth';
  import { useRouter } from 'vue-router';
  import { computed } from 'vue';

  const auth = useAuthStore();
  const router = useRouter();

  const navItems = computed(() => {
    const items = [
      { label: 'Dashboard', to: '/' },
      { label: 'Produtos', to: '/products' },
    ];

    if (auth.canSell) {
      items.push({ label: 'Vendas', to: '/sales' });
    }

    items.push({ label: 'Clientes', to: '/customers' });

    return items;
  });
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100 flex">
    <aside class="w-64 bg-zinc-900 border-r border-zinc-800 p-4 flex flex-col">
      <h1 class="text-lg font-bold text-emerald-400 mb-6">FlowERP</h1>

      <nav class="flex-1 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="block px-3 py-2 rounded-lg text-sm hover:bg-zinc-800 transition-colors"
          active-class="bg-zinc-800 text-white"
        >
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="border-t border-zinc-800 pt-4">
        <p class="text-xs text-zinc-500">{{ auth.user?.name }}</p>
        <p class="text-xs text-zinc-600 mb-2">{{ auth.user?.role }}</p>
        <button
          class="text-xs text-red-400 hover:text-red-300"
          @click="
            auth.logout();
            router.push('/login');
          "
        >
          Sair
        </button>
      </div>
    </aside>

    <main class="flex-1 overflow-auto">
      <RouterView />
    </main>
  </div>
</template>

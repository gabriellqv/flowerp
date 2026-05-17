<script setup lang="ts">
  /**
   * Feed de atividade recente do dashboard.
   *
   * Exibe as últimas ações registradas no sistema (vendas,
   * atualizações de produto, etc.) com nome do usuário,
   * descrição da ação e timestamp relativo.
   */
  import { ref, onMounted } from 'vue';
  import { RouterLink } from 'vue-router';
  import api from '@/services/api';
  import type { IActivityLogEntry } from '@/types';
  import { ClipboardList } from 'lucide-vue-next';

  const activities = ref<IActivityLogEntry[]>([]);
  const loading = ref(true);

  /** Mapa de ações para descrições legíveis em pt-BR. */
  const actionLabels: Record<string, string> = {
    SALE_CREATED: 'registrou uma venda',
    PRODUCT_CREATED: 'cadastrou um produto',
    PRODUCT_UPDATED: 'atualizou um produto',
    PRODUCT_DELETED: 'removeu um produto',
    CUSTOMER_CREATED: 'cadastrou um cliente',
  };

  /**
   * Formata timestamp para exibição relativa (ex: "há 2h").
   */
  function timeAgo(dateStr: string): string {
    const diff = Date.now() - new Date(dateStr).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'agora';
    if (minutes < 60) return `há ${minutes}min`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `há ${hours}h`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `há ${days}d`;
    const months = Math.floor(days / 30);
    return `há ${months}m`;
  }

  /**
   * Retorna o nome do cliente a partir dos detalhes da atividade.
   */
  function getCustomerName(activity: IActivityLogEntry): string | undefined {
    if (!activity.details) return undefined;
    return activity.details.customer_name as string | undefined;
  }

  /**
   * Retorna o total formatado a partir dos detalhes da atividade.
   */
  function getTotal(activity: IActivityLogEntry): string | undefined {
    if (!activity.details || !activity.details.total) return undefined;
    return Number(activity.details.total).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
    });
  }

  onMounted(async () => {
    const { data } = await api.get<IActivityLogEntry[]>('/dashboard/activity-feed');
    activities.value = data.slice(0, 6);
    loading.value = false;
  });
</script>

<template>
  <div
    class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] p-card rounded-card relative overflow-hidden transition-all duration-300 hover:shadow-[0_8px_32px_-8px_rgba(0,0,0,0.15)] hover:-translate-y-0.5 h-full"
  >
    <!-- Reflexo suave de luz na borda superior para reforçar o vidro -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
    ></div>
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-body font-semibold">Atividade Recente</h3>
      <RouterLink
        to="/activities"
        class="text-xs text-primary-text hover:underline cursor-pointer transition-colors"
        title="Ver todas as atividades"
      >
        Ver todas
      </RouterLink>
    </div>

    <div v-if="loading" class="flex flex-col gap-4 mt-2">
      <div v-for="i in 5" :key="i" class="flex items-start gap-3">
        <div
          class="w-8 h-8 rounded-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse shrink-0"
        ></div>
        <div class="flex-1 space-y-2 py-1">
          <div class="h-4 bg-zinc-200/50 dark:bg-zinc-700/30 rounded w-3/4 animate-pulse"></div>
          <div class="h-3 bg-zinc-200/50 dark:bg-zinc-700/30 rounded w-1/2 animate-pulse"></div>
        </div>
      </div>
    </div>

    <div
      v-else-if="activities.length === 0"
      class="flex-1 flex flex-col items-center justify-center gap-2 text-tertiary"
    >
      <ClipboardList :size="32" class="opacity-30" />
      <span class="text-small">Nenhuma atividade registrada.</span>
    </div>

    <ul v-else class="space-y-3">
      <li v-for="activity in activities" :key="activity.id" class="flex items-start gap-3">
        <div
          class="w-8 h-8 rounded-full bg-surface-secondary flex items-center justify-center shrink-0 text-small font-semibold text-primary-text"
        >
          {{ activity.user?.name?.charAt(0) ?? '?' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-small text-[var(--color-text-primary)]">
            <span class="font-medium text-primary-text">
              {{ activity.user?.name ?? 'Sistema' }}
            </span>
            {{ actionLabels[activity.action] ?? activity.action }}
          </p>
          <p
            v-if="getCustomerName(activity)"
            class="text-small text-[var(--color-text-primary)] mt-0.5"
          >
            Cliente:
            <span class="text-primary-text">{{ getCustomerName(activity) }}</span>
            <span v-if="getTotal(activity)">
              &middot;
              <span class="font-mono text-primary-text">R$ {{ getTotal(activity) }}</span>
            </span>
          </p>
          <span class="text-small text-[var(--color-text-primary)] block mt-0.5">
            {{ timeAgo(activity.created_at) }}
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

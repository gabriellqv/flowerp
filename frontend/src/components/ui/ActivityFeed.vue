<script setup lang="ts">
  /**
   * Feed de atividade recente do dashboard.
   *
   * Exibe as últimas ações registradas no sistema (vendas,
   * atualizações de produto, etc.) com nome do usuário,
   * descrição da ação e timestamp relativo.
   */
  import { ref, onMounted } from 'vue';
  import api from '@/services/api';
  import type { IActivityLogEntry } from '@/types';

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
    activities.value = data;
    loading.value = false;
  });
</script>

<template>
  <div class="bg-surface rounded-card border border-border p-card">
    <h3 class="text-body font-semibold mb-4">Atividade Recente</h3>

    <div v-if="loading" class="text-tertiary">Carregando...</div>

    <div v-else-if="activities.length === 0" class="text-tertiary text-small">
      Nenhuma atividade registrada.
    </div>

    <ul v-else class="space-y-3">
      <li v-for="activity in activities" :key="activity.id" class="flex items-start gap-3">
        <div
          class="w-8 h-8 rounded-full bg-surface-secondary flex items-center justify-center shrink-0 text-small font-semibold text-primary-text"
        >
          {{ activity.user?.name?.charAt(0) ?? '?' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-small">
            <span class="font-medium">{{ activity.user?.name ?? 'Sistema' }}</span>
            {{ actionLabels[activity.action] ?? activity.action }}
          </p>
          <p v-if="getCustomerName(activity)" class="text-small text-tertiary truncate">
            Cliente: {{ getCustomerName(activity) }}
            <span v-if="getTotal(activity)">&middot; R$ {{ getTotal(activity) }}</span>
          </p>
          <span class="text-small text-disabled">{{ timeAgo(activity.created_at) }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

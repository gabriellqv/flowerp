<script setup lang="ts">
  /**
   * Página de histórico completo de atividades do sistema.
   *
   * Exibe todas as ações registradas (vendas, cadastros, atualizações)
   * com busca por nome do usuário, filtro por tipo de ação e
   * paginação via DataTable. Acessada pelo link "Ver todas"
   * do feed de atividades no dashboard.
   */
  import { ref, onMounted, watch, computed } from 'vue';
  import api from '@/services/api';
  import type { IActivityLogEntry, PaginatedResponse } from '@/types';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import { ClipboardList } from 'lucide-vue-next';

  const activities = ref<IActivityLogEntry[]>([]);
  const total = ref(0);
  const page = ref(1);
  const perPage = ref(15);
  const loading = ref(true);
  const refreshing = ref(false);
  const search = ref('');
  const selectedAction = ref('');
  let abortController: AbortController | null = null;

  /** Mapa de ações para descrições legíveis em pt-BR. */
  const actionLabels: Record<string, string> = {
    SALE_CREATED: 'Venda registrada',
    PRODUCT_CREATED: 'Produto cadastrado',
    PRODUCT_UPDATED: 'Produto atualizado',
    PRODUCT_DELETED: 'Produto removido',
    CUSTOMER_CREATED: 'Cliente cadastrado',
  };

  interface SelectOption {
    label: string;
    value: string;
  }

  const actionOptions = computed<SelectOption[]>(() => [
    { label: 'Todas as ações', value: '' },
    ...Object.entries(actionLabels).map(([value, label]) => ({ label, value })),
  ]);

  const columns = [
    { key: 'user', label: 'Usuário' },
    { key: 'action', label: 'Ação' },
    { key: 'details', label: 'Detalhes' },
    { key: 'created_at', label: 'Data' },
  ];

  /**
   * Formata timestamp para exibição legível.
   */
  function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

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
   * Retorna o nome do cliente a partir dos detalhes.
   */
  function getCustomerName(activity: IActivityLogEntry): string | undefined {
    if (!activity.details) return undefined;
    return activity.details.customer_name as string | undefined;
  }

  /**
   * Retorna o total formatado.
   */
  function getTotal(activity: IActivityLogEntry): string | undefined {
    if (!activity.details || !activity.details.total) return undefined;
    return Number(activity.details.total).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
    });
  }

  /**
   * Retorna as classes CSS da badge de tipo de ação.
   */
  function actionBadgeClass(action: string): string {
    const map: Record<string, string> = {
      SALE_CREATED: 'bg-primary/10 text-primary-text border-primary/20',
      PRODUCT_CREATED: 'bg-info-bg text-info border-info-border',
      PRODUCT_UPDATED: 'bg-warning-bg text-warning border-warning-border',
      PRODUCT_DELETED: 'bg-error-bg text-error border-error-border',
      CUSTOMER_CREATED: 'bg-success-bg text-primary-text border-success-border',
    };
    return map[action] ?? 'bg-surface-elevated text-secondary border-border';
  }

  async function fetchActivities() {
    if (abortController) abortController.abort();
    const controller = new AbortController();
    abortController = controller;

    if (activities.value.length > 0) {
      refreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const params: Record<string, unknown> = {
        page: page.value,
        per_page: perPage.value,
        search: search.value || undefined,
        action: selectedAction.value || undefined,
      };
      const { data } = await api.get<PaginatedResponse<IActivityLogEntry>>(
        '/dashboard/activity-log',
        { params, signal: controller.signal },
      );
      activities.value = data.data;
      total.value = data.total;
    } catch (e: unknown) {
      const err = e as { code?: string };
      if (err.code === 'ERR_CANCELED') return;
      throw e;
    } finally {
      if (!controller.signal.aborted) {
        loading.value = false;
        refreshing.value = false;
      }
    }
  }

  onMounted(fetchActivities);

  watch(page, fetchActivities);
  watch(perPage, () => {
    page.value = 1;
    fetchActivities();
  });
  watch(search, () => {
    page.value = 1;
    fetchActivities();
  });
  watch(selectedAction, () => {
    page.value = 1;
    fetchActivities();
  });
</script>

<template>
  <PageContainer>
    <PageHeader title="Histórico de Atividades" />

    <div class="max-w-xs">
      <AppSelect v-model="selectedAction" :options="actionOptions" />
    </div>

    <DataTable
      :columns="columns"
      :data="activities"
      :total="total"
      :page="page"
      :per-page="perPage"
      :loading="loading"
      :refreshing="refreshing"
      @update:page="page = $event"
      @update:per-page="perPage = $event"
      @search="search = $event"
    >
      <template #empty>
        <div class="flex flex-col items-center justify-center gap-3">
          <ClipboardList :size="40" class="text-tertiary opacity-30" />
          <p class="text-tertiary text-sm">Nenhuma atividade encontrada.</p>
        </div>
      </template>

      <template #cell-user="{ row }">
        <div class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-full bg-surface-secondary flex items-center justify-center shrink-0 text-xs font-semibold text-primary-text"
          >
            {{ row.user?.name?.charAt(0) ?? '?' }}
          </div>
          <span class="font-medium">{{ row.user?.name ?? 'Sistema' }}</span>
        </div>
      </template>

      <template #cell-action="{ row }">
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border"
          :class="actionBadgeClass(row.action)"
        >
          {{ actionLabels[row.action] ?? row.action }}
        </span>
      </template>

      <template #cell-details="{ row }">
        <div class="text-sm">
          <template v-if="getCustomerName(row)">
            <span class="text-secondary">Cliente:</span>
            <span class="text-primary-text ml-1">{{ getCustomerName(row) }}</span>
            <span v-if="getTotal(row)" class="ml-1.5 font-mono text-primary-text">
              R$ {{ getTotal(row) }}
            </span>
          </template>
          <span v-else class="text-secondary">{{ row.entity ?? '-' }}</span>
        </div>
      </template>

      <template #cell-created_at="{ row }">
        <div class="flex flex-col">
          <span class="text-sm">{{ formatDate(row.created_at) }}</span>
          <span class="text-xs text-secondary">{{ timeAgo(row.created_at) }}</span>
        </div>
      </template>
    </DataTable>
  </PageContainer>
</template>

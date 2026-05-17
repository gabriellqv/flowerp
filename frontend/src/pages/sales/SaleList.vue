<script setup lang="ts">
  /**
   * Listagem de vendas com busca, cliente, status e navegação.
   *
   * Exibe ID truncado, nome do cliente, nome do vendedor, total
   * formatado, data localizada e badge de status.
   * Botão "Nova Venda" condicionado a canSell com ícone.
   * Utiliza formatCurrency do util compartilhado e AbortController
   * para evitar race condition.
   */
  import { ref, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { ISale, IPaginatedResponse } from '@/types';
  import { formatCurrency } from '@/utils/format';
  import { useAuthStore } from '@/stores/auth';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import { Plus, ShoppingCart } from 'lucide-vue-next';

  const router = useRouter();
  const auth = useAuthStore();

  const sales = ref<ISale[]>([]);
  const total = ref(0);
  const page = ref(1);
  const perPage = ref(20);
  const loading = ref(true);
  const refreshing = ref(false);
  const search = ref('');
  let abortController: AbortController | null = null;

  const columns = [
    { key: 'id', label: 'Venda' },
    { key: 'customer', label: 'Cliente' },
    { key: 'seller', label: 'Vendedor' },
    { key: 'total_amount', label: 'Total', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Data', sortable: true },
  ];

  async function fetchSales() {
    if (abortController) {
      abortController.abort();
    }

    const controller = new AbortController();
    abortController = controller;

    if (sales.value.length > 0) {
      refreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const { data } = await api.get<IPaginatedResponse<ISale>>('/sales', {
        params: { page: page.value, search: search.value || undefined },
        signal: controller.signal,
      });
      sales.value = data.data;
      total.value = data.meta?.total || 0;
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

  onMounted(fetchSales);
  watch(page, fetchSales);
  watch(perPage, () => {
    page.value = 1;
    fetchSales();
  });
  watch(search, () => {
    page.value = 1;
    fetchSales();
  });

  /**
   * Define a classe CSS da badge de status da venda.
   */
  function statusBadgeClass(status: string): string {
    if (status === 'CANCELLED') return 'bg-error/10 text-error border-error/20';
    return 'bg-success-bg text-primary-text border-success-border';
  }

  function statusLabel(status: string): string {
    if (status === 'CANCELLED') return 'Cancelada';
    return 'Concluída';
  }

  function viewSale(sale: ISale) {
    router.push(`/sales/${sale.id}`);
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Vendas">
      <template v-if="auth.canSell" #actions>
        <AppButton size="sm" @click="router.push('/sales/new')">
          <Plus :size="16" class="mr-1.5" />
          Nova Venda
        </AppButton>
      </template>
    </PageHeader>

    <DataTable
      :columns="columns"
      :data="sales"
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
          <ShoppingCart :size="40" class="text-tertiary opacity-30" />
          <p class="text-tertiary text-sm">Nenhuma venda encontrada.</p>
        </div>
      </template>

      <template #cell-id="{ row }">
        <button
          class="font-mono cursor-pointer hover:text-primary transition-colors underline-offset-2 hover:underline"
          @click="viewSale(row)"
        >
          #{{ row.id.slice(0, 8) }}
        </button>
      </template>
      <template #cell-customer="{ row }">
        {{ row.customer?.name || '-' }}
      </template>
      <template #cell-seller="{ row }">
        {{ row.seller?.name || '-' }}
      </template>
      <template #cell-total_amount="{ row }">
        <span class="font-mono">{{ formatCurrency(row.total_amount) }}</span>
      </template>
      <template #cell-status="{ row }">
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border"
          :class="statusBadgeClass(row.status)"
        >
          {{ statusLabel(row.status) }}
        </span>
      </template>
      <template #cell-created_at="{ row }">
        {{ new Date(row.created_at).toLocaleDateString('pt-BR') }}
      </template>
    </DataTable>
  </PageContainer>
</template>

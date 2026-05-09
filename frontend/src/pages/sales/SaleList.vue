<script setup lang="ts">
  /**
   * Listagem de vendas com DataTable e navegacao para nova venda.
   *
   * Exibe ID truncado, nome do vendedor, total formatado
   * e data localizada. Botao "Nova Venda" condicionado a `canSell`.
   */
  import { ref, onMounted, watch } from 'vue';
  import api from '@/services/api';
  import type { Sale, PaginatedResponse } from '@/types';
  import DataTable from '@/components/ui/DataTable.vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';

  const router = useRouter();
  const auth = useAuthStore();

  const sales = ref<Sale[]>([]);
  const total = ref(0);
  const page = ref(1);
  const loading = ref(true);

  const columns = [
    { key: 'id', label: 'Venda' },
    { key: 'seller', label: 'Vendedor' },
    { key: 'total_amount', label: 'Total' },
    { key: 'created_at', label: 'Data' },
  ];

  async function fetchSales() {
    loading.value = true;
    const { data } = await api.get<PaginatedResponse<Sale>>('/sales', {
      params: { page: page.value },
    });
    sales.value = data.data;
    total.value = data.total;
    loading.value = false;
  }

  onMounted(fetchSales);
  watch(page, fetchSales);
</script>

<template>
  <PageContainer>
    <PageHeader title="Vendas">
      <template v-if="auth.canSell" #actions>
        <AppButton size="sm" @click="router.push('/sales/new')">Nova Venda</AppButton>
      </template>
    </PageHeader>

    <DataTable
      :columns="columns"
      :data="sales"
      :total="total"
      :page="page"
      :per-page="20"
      :loading="loading"
      @update:page="page = $event"
    >
      <template #cell-id="{ row }">#{{ row.id.slice(0, 8) }}</template>
      <template #cell-seller="{ row }">
        {{ row.seller?.name || '-' }}
      </template>
      <template #cell-total_amount="{ row }">R$ {{ Number(row.total_amount).toFixed(2) }}</template>
      <template #cell-created_at="{ row }">
        {{ new Date(row.created_at).toLocaleDateString('pt-BR') }}
      </template>
    </DataTable>
  </PageContainer>
</template>

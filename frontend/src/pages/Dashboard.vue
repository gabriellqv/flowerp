<script setup lang="ts">
  /**
   * Dashboard executivo com cards de KPI.
   *
   * Consome o endpoint /dashboard/summary e exibe metricas
   * de produtos, receita, alertas e vendas em cards com icones.
   */
  import { ref, onMounted } from 'vue';
  import api from '@/services/api';
  import type { DashboardSummary } from '@/types';
  import { Package, DollarSign, AlertTriangle, ShoppingCart } from 'lucide-vue-next';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import DashboardCard from '@/components/ui/DashboardCard.vue';

  const summary = ref<DashboardSummary | null>(null);
  const loading = ref(true);

  const cards = [
    {
      label: 'Produtos Ativos',
      key: 'active_products' as const,
      icon: Package,
      iconClass: 'w-6 h-6 text-primary-text',
    },
    {
      label: 'Receita do Mes',
      key: 'monthly_revenue' as const,
      icon: DollarSign,
      iconClass: 'w-6 h-6 text-primary-text',
      format: (v: number) => `R$ ${v.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
    },
    {
      label: 'Estoque Baixo',
      key: 'low_stock_count' as const,
      icon: AlertTriangle,
      iconClass: 'w-6 h-6 text-warning',
    },
    {
      label: 'Vendas do Mes',
      key: 'monthly_sales_count' as const,
      icon: ShoppingCart,
      iconClass: 'w-6 h-6 text-info',
    },
  ];

  onMounted(async () => {
    const { data } = await api.get<DashboardSummary>('/dashboard/summary');
    summary.value = data;
    loading.value = false;
  });
</script>

<template>
  <PageContainer>
    <PageHeader title="Dashboard" />

    <div v-if="loading" class="text-tertiary">Carregando...</div>

    <div v-else-if="summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <DashboardCard
        v-for="card in cards"
        :key="card.key"
        :label="card.label"
        :value="card.format ? card.format(Number(summary[card.key])) : String(summary[card.key])"
      >
        <template #icon>
          <component :is="card.icon" :class="card.iconClass" />
        </template>
      </DashboardCard>
    </div>
  </PageContainer>
</template>

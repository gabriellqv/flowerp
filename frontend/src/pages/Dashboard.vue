<script setup lang="ts">
  /**
   * Dashboard executivo com KPIs, gráfico de receita e feed de atividade.
   *
   * Consome o endpoint /dashboard/summary com comparativo mensal,
   * exibe cards com variação percentual, gráfico de receita com
   * seletor de período e feed de atividade recente em layout de 2 colunas.
   */
  import { ref, onMounted } from 'vue';
  import api from '@/services/api';
  import type { DashboardSummary } from '@/types';
  import { Package, DollarSign, AlertTriangle, ShoppingCart, XCircle } from 'lucide-vue-next';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import DashboardCard from '@/components/ui/DashboardCard.vue';
  import RevenueChart from '@/components/ui/RevenueChart.vue';
  import ActivityFeed from '@/components/ui/ActivityFeed.vue';

  const summary = ref<DashboardSummary | null>(null);
  const loading = ref(true);

  onMounted(async () => {
    const { data } = await api.get<DashboardSummary>('/dashboard/summary');
    summary.value = data;
    loading.value = false;
  });

  /**
   * Formata valor monetário em reais.
   */
  function formatCurrency(value: number): string {
    return `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Dashboard" />

    <div v-if="loading" class="text-tertiary">Carregando...</div>

    <template v-else-if="summary">
      <!-- Cards KPI -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <DashboardCard label="Produtos Ativos" :value="String(summary.active_products)">
          <template #icon>
            <Package class="w-6 h-6 text-primary-text" />
          </template>
        </DashboardCard>

        <DashboardCard
          label="Receita do Mês"
          :value="formatCurrency(summary.monthly_revenue)"
          :change="summary.revenue_change"
        >
          <template #icon>
            <DollarSign class="w-6 h-6 text-primary-text" />
          </template>
        </DashboardCard>

        <DashboardCard label="Estoque Baixo" :value="String(summary.low_stock_count)">
          <template #icon>
            <AlertTriangle class="w-6 h-6 text-warning" />
          </template>
        </DashboardCard>

        <DashboardCard label="Estoque Zerado" :value="String(summary.zero_stock_count)">
          <template #icon>
            <XCircle class="w-6 h-6 text-error" />
          </template>
        </DashboardCard>

        <DashboardCard
          label="Vendas do Mês"
          :value="String(summary.monthly_sales_count)"
          :change="summary.sales_change"
        >
          <template #icon>
            <ShoppingCart class="w-6 h-6 text-info" />
          </template>
        </DashboardCard>
      </div>

      <!-- Grid 2 colunas: Gráfico + Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
          <RevenueChart />
        </div>
        <div>
          <ActivityFeed />
        </div>
      </div>
    </template>
  </PageContainer>
</template>

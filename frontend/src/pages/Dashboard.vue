<script setup lang="ts">
  /**
   * Dashboard executivo com KPIs, gráficos e feed de atividade.
   *
   * Consome o endpoint /dashboard/summary com comparativo mensal,
   * exibe cards de KPI e múltiplos gráficos (Receita, Categoria, Top Produtos).
   */
  import { ref, onMounted, computed } from 'vue';
  import api from '@/services/api';
  import { useAuthStore } from '@/stores/auth';
  import type { DashboardSummary } from '@/types';
  import {
    Package,
    DollarSign,
    AlertTriangle,
    ShoppingCart,
    XCircle,
    TrendingUp,
    Database,
  } from 'lucide-vue-next';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import DashboardCard from '@/components/ui/DashboardCard.vue';
  import RevenueChart from '@/components/ui/RevenueChart.vue';
  import ActivityFeed from '@/components/ui/ActivityFeed.vue';
  import TopProductsChart from '@/components/ui/TopProductsChart.vue';
  import RevenueByCategoryChart from '@/components/ui/RevenueByCategoryChart.vue';

  const authStore = useAuthStore();
  const summary = ref<DashboardSummary | null>(null);
  const loading = ref(true);

  const greeting = computed(() => {
    const firstName = authStore.user?.name?.split(' ')[0] ?? 'Usuário';
    const hour = new Date().getHours();
    const salutation = hour < 12 ? 'Bom dia' : hour < 18 ? 'Boa tarde' : 'Boa noite';
    return `${salutation}, ${firstName}!`;
  });

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
    <PageHeader :title="greeting" />

    <div v-if="loading" class="text-tertiary">Carregando...</div>

    <template v-else-if="summary">
      <!-- Cards KPI Principais -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <DashboardCard
          label="Receita do Mês"
          :value="formatCurrency(summary.monthly_revenue)"
          :change="summary.revenue_change"
        >
          <template #icon>
            <DollarSign class="w-6 h-6 text-primary-text" />
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

        <DashboardCard label="Ticket Médio" :value="formatCurrency(summary.average_ticket)">
          <template #icon>
            <TrendingUp class="w-6 h-6 text-primary-text" />
          </template>
        </DashboardCard>

        <DashboardCard label="Estoque Zerado" :value="String(summary.zero_stock_count)">
          <template #icon>
            <XCircle class="w-6 h-6 text-error" />
          </template>
        </DashboardCard>
      </div>

      <!-- Cards KPI Secundários -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <DashboardCard label="Estoque Baixo" :value="String(summary.low_stock_count)">
          <template #icon>
            <AlertTriangle class="w-6 h-6 text-warning" />
          </template>
        </DashboardCard>

        <DashboardCard label="Produtos Ativos" :value="String(summary.active_products)">
          <template #icon>
            <Package class="w-6 h-6 text-primary-text" />
          </template>
        </DashboardCard>

        <DashboardCard label="Valor em Estoque" :value="formatCurrency(summary.total_stock_value)">
          <template #icon>
            <Database class="w-6 h-6 text-primary-text" />
          </template>
        </DashboardCard>
      </div>

      <!-- Gráfico Principal + Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <div class="lg:col-span-2">
          <RevenueChart />
        </div>
        <div>
          <ActivityFeed />
        </div>
      </div>

      <!-- Gráficos Secundários -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
          <RevenueByCategoryChart />
        </div>
        <div>
          <TopProductsChart />
        </div>
      </div>
    </template>
  </PageContainer>
</template>

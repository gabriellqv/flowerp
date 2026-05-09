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

  const summary = ref<DashboardSummary | null>(null);
  const loading = ref(true);

  onMounted(async () => {
    const { data } = await api.get<DashboardSummary>('/dashboard/summary');
    summary.value = data;
    loading.value = false;
  });
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    <div v-if="loading" class="text-zinc-500">Carregando...</div>

    <div v-else-if="summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm text-zinc-400">Produtos Ativos</span>
          <Package class="w-5 h-5 text-indigo-400" />
        </div>
        <p class="text-2xl font-bold">{{ summary.active_products }}</p>
      </div>

      <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm text-zinc-400">Receita do Mes</span>
          <DollarSign class="w-5 h-5 text-emerald-400" />
        </div>
        <p class="text-2xl font-bold">
          R$
          {{ summary.monthly_revenue.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
        </p>
      </div>

      <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm text-zinc-400">Estoque Baixo</span>
          <AlertTriangle class="w-5 h-5 text-amber-400" />
        </div>
        <p class="text-2xl font-bold">{{ summary.low_stock_count }}</p>
      </div>

      <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm text-zinc-400">Vendas do Mes</span>
          <ShoppingCart class="w-5 h-5 text-sky-400" />
        </div>
        <p class="text-2xl font-bold">{{ summary.monthly_sales_count }}</p>
      </div>
    </div>
  </div>
</template>

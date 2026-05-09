<script setup lang="ts">
  /**
   * Grafico de Receita por Categoria (Doughnut).
   *
   * Exibe a distribuicao da receita no mes atual por categoria.
   * Utiliza vue-chartjs com Chart.js.
   */
  import { ref, onMounted } from 'vue';
  import { Doughnut } from 'vue-chartjs';
  import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
  import api from '@/services/api';
  import type { IChartDataPoint } from '@/types';

  ChartJS.register(ArcElement, Tooltip, Legend);

  const chartData = ref<IChartDataPoint[]>([]);
  const loading = ref(true);

  onMounted(async () => {
    const { data } = await api.get<IChartDataPoint[]>('/dashboard/revenue-by-category');
    chartData.value = data;
    loading.value = false;
  });

  function formatCurrency(value: number): string {
    return `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
  }

  // Cores personalizadas para o grafico
  const colors = [
    '#059669', // emerald
    '#3b82f6', // blue
    '#f59e0b', // amber
    '#8b5cf6', // violet
    '#ef4444', // red
    '#14b8a6', // teal
    '#f97316', // orange
    '#6366f1', // indigo
  ];
</script>

<template>
  <div class="bg-surface rounded-card border border-border p-card">
    <h3 class="text-body font-semibold mb-4">Receita por Categoria</h3>

    <div v-if="loading" class="h-48 flex items-center justify-center text-tertiary">
      Carregando...
    </div>

    <div
      v-else-if="chartData.length === 0"
      class="h-48 flex items-center justify-center text-tertiary text-small"
    >
      Nenhum dado disponível.
    </div>

    <div v-else class="h-48 flex justify-center">
      <Doughnut
        :data="{
          labels: chartData.map((d) => d.label),
          datasets: [
            {
              data: chartData.map((d) => d.value),
              backgroundColor: colors.slice(0, chartData.length),
              borderWidth: 1,
            },
          ],
        }"
        :options="{
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            tooltip: {
              callbacks: {
                label: (ctx: { raw: unknown }) => formatCurrency(ctx.raw as number),
              },
            },
            legend: {
              position: 'right',
              labels: {
                boxWidth: 12,
                font: { size: 11 },
              },
            },
          },
        }"
      />
    </div>
  </div>
</template>

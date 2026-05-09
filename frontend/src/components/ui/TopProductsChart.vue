<script setup lang="ts">
  /**
   * Grafico de Top Produtos (Barras Horizontais).
   *
   * Exibe os 5 produtos mais vendidos no mes atual.
   * Utiliza vue-chartjs com Chart.js.
   */
  import { ref, onMounted } from 'vue';
  import { Bar } from 'vue-chartjs';
  import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';
  import api from '@/services/api';
  import type { IChartDataPoint } from '@/types';

  ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

  const chartData = ref<IChartDataPoint[]>([]);
  const loading = ref(true);

  onMounted(async () => {
    const { data } = await api.get<IChartDataPoint[]>('/dashboard/top-products');
    chartData.value = data;
    loading.value = false;
  });
</script>

<template>
  <div class="bg-surface rounded-card border border-border p-card">
    <h3 class="text-body font-semibold mb-4">Top 5 Produtos Mais Vendidos</h3>

    <div v-if="loading" class="h-48 flex items-center justify-center text-tertiary">
      Carregando...
    </div>

    <div
      v-else-if="chartData.length === 0"
      class="h-48 flex items-center justify-center text-tertiary text-small"
    >
      Nenhum dado disponível.
    </div>

    <div v-else class="h-48">
      <Bar
        :data="{
          labels: chartData.map((d) => d.label),
          datasets: [
            {
              data: chartData.map((d) => d.value),
              backgroundColor: 'rgba(59, 130, 246, 0.6)',
              borderColor: '#3b82f6',
              borderWidth: 1,
              borderRadius: 4,
            },
          ],
        }"
        :options="{
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            tooltip: {
              callbacks: {
                label: (ctx: { raw: unknown }) => `${ctx.raw} unidades`,
              },
            },
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.05)' },
            },
            y: { grid: { display: false } },
          },
        }"
      />
    </div>
  </div>
</template>

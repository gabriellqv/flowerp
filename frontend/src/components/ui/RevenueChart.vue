<script setup lang="ts">
  /**
   * Gráfico de receita com suporte a múltiplos períodos.
   *
   * Exibe um bar chart da receita ao longo do tempo,
   * com seletor de período (7 dias, 6 meses, 12 meses).
   * Utiliza vue-chartjs com Chart.js para renderização.
   */
  import { ref, onMounted, watch } from 'vue';
  import { Bar } from 'vue-chartjs';
  import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';
  import api from '@/services/api';
  import type { IChartDataPoint } from '@/types';

  ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

  const periods = [
    { label: '7D', value: '7d' },
    { label: '6M', value: '6m' },
    { label: '12M', value: '12m' },
  ] as const;

  const activePeriod = ref('6m');
  const chartData = ref<IChartDataPoint[]>([]);
  const loading = ref(true);

  async function fetchChart() {
    loading.value = true;
    const { data } = await api.get<IChartDataPoint[]>(
      `/dashboard/revenue-chart?period=${activePeriod.value}`,
    );
    chartData.value = data;
    loading.value = false;
  }

  onMounted(fetchChart);
  watch(activePeriod, fetchChart);

  /**
   * Formata valor monetário para exibição no tooltip.
   */
  function formatCurrency(value: number): string {
    return `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
  }
</script>

<template>
  <div class="bg-surface rounded-card border border-border p-card">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-body font-semibold">Receita</h3>
      <div class="flex gap-1">
        <button
          v-for="p in periods"
          :key="p.value"
          class="px-3 py-1 text-small rounded-button-sm transition-colors cursor-pointer"
          :class="
            activePeriod === p.value
              ? 'bg-primary text-white'
              : 'text-secondary hover:bg-surface-elevated'
          "
          @click="activePeriod = p.value"
        >
          {{ p.label }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="h-48 flex items-center justify-center text-tertiary">
      Carregando...
    </div>

    <div v-else class="h-48">
      <Bar
        :data="{
          labels: chartData.map((d) => d.label),
          datasets: [
            {
              data: chartData.map((d) => d.value),
              backgroundColor: 'rgba(5, 150, 105, 0.6)',
              borderColor: '#059669',
              borderWidth: 1,
              borderRadius: 4,
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
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value: string | number) => `R$ ${Number(value).toLocaleString('pt-BR')}`,
              },
              grid: { color: 'rgba(0,0,0,0.05)' },
            },
            x: { grid: { display: false } },
          },
        }"
      />
    </div>
  </div>
</template>

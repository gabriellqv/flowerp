<script setup lang="ts">
  /**
   * Grafico de Top Produtos (Barras Horizontais).
   *
   * Exibe os 5 produtos mais vendidos no mes atual.
   * Barras crescem animadas a partir de zero ao carregar.
   * Utiliza vue-chartjs com Chart.js.
   */
  import { ref, onMounted, nextTick } from 'vue';
  import { Bar } from 'vue-chartjs';
  import type { ChartComponentRef } from 'vue-chartjs';
  import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';
  import type { ScriptableContext } from 'chart.js';
  import api from '@/services/api';
  import type { IChartDataPoint } from '@/types';
  import { glassTooltipConfig } from '@/utils/chartConfig';
  import { BarChart3 } from 'lucide-vue-next';

  ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

  const chartData = ref<IChartDataPoint[]>([]);
  const loading = ref(true);
  const chartReady = ref(false);
  const barRef = ref<ChartComponentRef<'bar'> | null>(null);

  /** Cria gradiente horizontal para as barras de top produtos. */
  function createHorizontalGradient(context: ScriptableContext<'bar'>) {
    const chart = context.chart;
    const { ctx, chartArea } = chart;
    if (!chartArea) return 'rgba(5, 150, 105, 0.6)';
    const gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.2)');
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0.8)');
    return gradient;
  }

  /**
   * Trunca labels do eixo Y para no máximo 16 caracteres.
   *
   * Usa o índice numérico recebido pelo Chart.js para buscar
   * o label correspondente no array de dados carregados.
   */
  function truncateTickLabel(value: string | number): string {
    const label = chartData.value[value as number]?.label ?? String(value);
    if (label.length > 16) {
      return label.substring(0, 16) + '...';
    }
    return label;
  }

  onMounted(async () => {
    try {
      const { data } = await api.get<IChartDataPoint[]>('/dashboard/top-products');
      chartData.value = data || [];
    } catch (e) {
      console.error('Erro ao carregar top products:', e);
      chartData.value = [];
    } finally {
      loading.value = false;
      chartReady.value = true;
      await nextTick();

      const chart = barRef.value?.chart;
      if (chart && chartData.value.length > 0) {
        chart.data.datasets[0].data = chartData.value.map((d) => d.value);
        chart.update('active');
      }
    }
  });
</script>

<template>
  <div
    class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] p-card rounded-card relative overflow-hidden transition-all duration-300 hover:shadow-[0_8px_32px_-8px_rgba(0,0,0,0.15)] hover:-translate-y-0.5"
  >
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
    ></div>
    <h3 class="text-body font-semibold mb-4">Top 5 Produtos Mais Vendidos</h3>

    <div v-if="loading" class="h-48 flex flex-col justify-between gap-3 px-2 py-2">
      <div
        class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[80%] delay-75"
      ></div>
      <div
        class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[60%] delay-100"
      ></div>
      <div
        class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[90%] delay-150"
      ></div>
      <div
        class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[50%] delay-200"
      ></div>
      <div
        class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[70%] delay-300"
      ></div>
    </div>

    <div
      v-else-if="chartData.length === 0"
      class="h-48 flex flex-col items-center justify-center gap-2 text-tertiary"
    >
      <BarChart3 :size="32" class="opacity-30" />
      <span class="text-small">Nenhuma venda registrada neste mes.</span>
    </div>

    <div v-else class="h-48">
      <Bar
        v-if="chartReady"
        ref="barRef"
        :data="{
          labels: chartData.map((d) => d.label),
          datasets: [
            {
              data: new Array(chartData.length).fill(0),
              backgroundColor: createHorizontalGradient,
              borderColor: '#059669',
              borderWidth: 1,
              borderRadius: 6,
              hoverBackgroundColor: '#059669',
            },
          ],
        }"
        :options="{
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          animation: {
            duration: 800,
            easing: 'easeOutQuart',
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...glassTooltipConfig,
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
            y: {
              grid: { display: false },
              ticks: {
                callback: truncateTickLabel,
              },
            },
          },
        }"
      />
    </div>
  </div>
</template>

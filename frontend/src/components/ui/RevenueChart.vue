<script setup lang="ts">
  /**
   * Gráfico de receita com suporte a múltiplos períodos.
   *
   * Exibe um bar chart da receita ao longo do tempo,
   * com seletor de período (7 dias, 6 meses, 12 meses).
   * Utiliza vue-chartjs com Chart.js para renderização.
   *
   * Carregamento em duas fases:
   * - Skeleton (barras pulsantes) no primeiro carregamento.
   * - Animação de barras crescendo ao montar.
   * - Transição nativa do Chart.js na troca de período.
   */
  import { ref, onMounted, watch, nextTick } from 'vue';
  import { Bar } from 'vue-chartjs';
  import type { ChartComponentRef } from 'vue-chartjs';
  import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js';
  import api from '@/services/api';
  import { formatCurrency } from '@/utils/format';
  import type { IChartDataPoint } from '@/types';
  import { glassTooltipConfig } from '@/utils/chartConfig';
  import { TrendingUp } from 'lucide-vue-next';

  ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

  const periods = [
    { label: '7D', value: '7d' },
    { label: '6M', value: '6m' },
    { label: '12M', value: '12m' },
  ] as const;

  const activePeriod = ref('6m');
  const chartData = ref<IChartDataPoint[]>([]);
  const chartValues = ref<number[]>([]);
  const initialLoading = ref(true);
  const chartMounted = ref(false);
  const refreshing = ref(false);
  const barRef = ref<ChartComponentRef<'bar'> | null>(null);

  async function fetchChart() {
    if (barRef.value?.chart) {
      refreshing.value = true;
    }

    const { data } = await api.get<IChartDataPoint[]>(
      `/dashboard/revenue-chart?period=${activePeriod.value}`,
    );
    const values = data.map((d) => d.value);

    chartData.value = data;
    initialLoading.value = false;
    refreshing.value = false;

    if (!chartMounted.value) {
      chartMounted.value = true;
      chartValues.value = new Array(values.length).fill(0);
      await nextTick();
    }

    chartValues.value = values;
  }

  onMounted(fetchChart);
  watch(activePeriod, fetchChart);
</script>

<template>
  <div
    class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] p-card rounded-card relative overflow-hidden transition-all duration-300 hover:shadow-[0_8px_32px_-8px_rgba(0,0,0,0.15)] hover:-translate-y-0.5"
  >
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
    ></div>
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
          :disabled="refreshing"
          @click="activePeriod = p.value"
        >
          {{ p.label }}
        </button>
      </div>
    </div>

    <div v-if="initialLoading" class="h-48 flex items-end justify-between gap-4 px-4 pb-6 pt-4">
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[40%]"
      ></div>
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[70%]"
      ></div>
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[30%]"
      ></div>
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[90%]"
      ></div>
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[50%]"
      ></div>
      <div
        class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[80%]"
      ></div>
    </div>

    <div
      v-else-if="chartData.length === 0"
      class="h-48 flex flex-col items-center justify-center gap-2 text-tertiary"
    >
      <TrendingUp :size="32" class="opacity-30" />
      <span class="text-small">Nenhuma receita no periodo selecionado.</span>
    </div>

    <div v-else class="h-48">
      <Bar
        v-if="chartMounted"
        ref="barRef"
        :data="{
          labels: chartData.map((d) => d.label),
          datasets: [
            {
              data: chartValues,
              backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) return 'rgba(5, 150, 105, 0.6)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(5, 150, 105, 0.1)');
                gradient.addColorStop(1, 'rgba(5, 150, 105, 0.8)');
                return gradient;
              },
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
          animation: {
            duration: 600,
            easing: 'easeOutQuart',
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...glassTooltipConfig,
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

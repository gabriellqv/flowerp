<script setup lang="ts">
  /**
   * Grafico de Receita por Categoria (Doughnut).
   *
   * Exibe a distribuicao da receita no mes atual por categoria.
   * A rosquinha anima rotacionando e expandindo ao carregar.
   * Utiliza vue-chartjs com Chart.js.
   */
  import { ref, onMounted, nextTick } from 'vue';
  import { Doughnut } from 'vue-chartjs';
  import type { ChartComponentRef } from 'vue-chartjs';
  import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
  import api from '@/services/api';
  import type { IChartDataPoint } from '@/types';
  import { glassTooltipConfig } from '@/utils/chartConfig';
  import { PieChart } from 'lucide-vue-next';

  ChartJS.register(ArcElement, Tooltip, Legend);

  const chartData = ref<IChartDataPoint[]>([]);
  const loading = ref(true);
  const chartReady = ref(false);
  const doughnutRef = ref<ChartComponentRef<'doughnut'> | null>(null);

  onMounted(async () => {
    const { data } = await api.get<IChartDataPoint[]>('/dashboard/revenue-by-category');
    chartData.value = data;
    loading.value = false;

    chartReady.value = true;
    await nextTick();

    const chart = doughnutRef.value?.chart;
    if (chart && data.length > 0) {
      chart.data.datasets[0].data = data.map((d) => d.value);
      chart.update('active');
    }
  });

  function formatCurrency(value: number): string {
    return `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
  }

  const colors = [
    'rgba(5, 150, 105, 0.8)',
    'rgba(59, 130, 246, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(139, 92, 246, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(20, 184, 166, 0.8)',
    'rgba(249, 115, 22, 0.8)',
    'rgba(99, 102, 241, 0.8)',
  ];
</script>

<template>
  <div
    class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] p-card rounded-card relative overflow-hidden transition-all duration-300 hover:shadow-[0_8px_32px_-8px_rgba(0,0,0,0.15)] hover:-translate-y-0.5"
  >
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
    ></div>
    <h3 class="text-body font-semibold mb-4">Receita por Categoria</h3>

    <div v-if="loading" class="h-52 flex items-center justify-center">
      <div
        class="w-40 h-40 border-[16px] border-zinc-200/50 dark:border-zinc-700/30 rounded-full animate-pulse"
      ></div>
    </div>

    <div
      v-else-if="chartData.length === 0"
      class="h-52 flex flex-col items-center justify-center gap-2 text-tertiary"
    >
      <PieChart :size="32" class="opacity-30" />
      <span class="text-small">Nenhuma categoria com receita.</span>
    </div>

    <div v-else class="h-52 flex justify-start mt-2">
      <div class="w-full">
        <Doughnut
          v-if="chartReady"
          ref="doughnutRef"
          :data="{
            labels: chartData.map((d) => d.label || 'Sem Categoria'),
            datasets: [
              {
                data: new Array(chartData.length).fill(0),
                backgroundColor: chartData.map((_, i) => colors[i % colors.length]),
                borderWidth: 1,
                borderColor: 'rgba(255, 255, 255, 0.1)',
                hoverOffset: 6,
              },
            ],
          }"
          :options="{
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              animateRotate: true,
              animateScale: true,
              duration: 1000,
              easing: 'easeOutQuart',
            },
            layout: {
              padding: 0,
            },
            plugins: {
              tooltip: {
                ...glassTooltipConfig,
                displayColors: true,
                callbacks: {
                  label: (ctx: { raw: unknown }) => formatCurrency(ctx.raw as number),
                },
              },
              legend: {
                position: 'right',
                labels: {
                  boxWidth: 12,
                  font: { size: 11 },
                  padding: 8,
                },
              },
            },
          }"
        />
      </div>
    </div>
  </div>
</template>

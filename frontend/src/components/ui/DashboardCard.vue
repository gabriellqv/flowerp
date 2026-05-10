<script setup lang="ts">
  /**
   * Card de KPI para o Dashboard.
   *
   * Exibe icone grande em circulo com sombra a esquerda,
   * rotulo, valor numerico e variacao percentual a direita.
   * Usa slot para o icone (componente lucide-vue-next).
   *
   * Anima o valor numérico de 0 até o valor final usando
   * o composable useCountUp para um efeito de odômetro.
   */
  import { computed } from 'vue';
  import { useCountUp } from '@/composables/useCountUp';

  const props = defineProps<{
    label: string;
    value: string | number;
    change?: number | null;
  }>();

  /**
   * Extrai o número puro da string de valor (ex: "R$ 45.123,00" -> 45123).
   */
  const numericTarget = computed(() => {
    const raw = String(props.value).replace(/[^\d.,]/g, '');
    const normalized = raw.replace(/\./g, '').replace(',', '.');
    return parseFloat(normalized) || 0;
  });

  const animatedNumber = useCountUp(() => numericTarget.value, 900);

  /** Anima o percentual de variação (positivo ou negativo). */
  const animatedChange = useCountUp(() => props.change ?? 0, 700);

  /**
   * Detecta se o valor é monetário (começa com "R$").
   */
  const isCurrency = computed(() => String(props.value).startsWith('R$'));

  /**
   * Formata o valor animado no mesmo formato do valor original.
   */
  const displayValue = computed(() => {
    if (isCurrency.value) {
      return `R$ ${animatedNumber.value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })}`;
    }
    return Math.round(animatedNumber.value).toLocaleString('pt-BR');
  });

  /**
   * Formata o percentual animado com sinal e 1 casa decimal.
   */
  const displayChange = computed(() => {
    const val = animatedChange.value;
    const sign = val >= 0 ? '+' : '';
    return `${sign}${val.toFixed(1)}%`;
  });
</script>

<template>
  <div
    class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] p-4 flex flex-col items-start gap-2 rounded-card relative overflow-hidden transition-all duration-300 hover:shadow-[0_8px_32px_-8px_rgba(0,0,0,0.15)] hover:-translate-y-0.5"
  >
    <!-- Reflexo suave de luz na borda superior para reforçar o vidro -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
    ></div>

    <div class="flex items-center gap-2 w-full">
      <div
        class="w-8 h-8 rounded-full bg-surface-secondary flex items-center justify-center shrink-0 shadow-sm"
      >
        <slot name="icon" />
      </div>
      <span class="text-sm font-medium text-[var(--color-text-secondary)] truncate flex-1">
        {{ label }}
      </span>
    </div>

    <div class="mt-1 w-full flex items-baseline justify-between">
      <p
        class="text-lg font-semibold font-mono text-[var(--color-text-primary)] truncate"
        :title="String(value)"
      >
        {{ displayValue }}
      </p>
      <span
        v-if="change !== undefined && change !== null"
        class="text-small font-medium font-mono"
        :class="change >= 0 ? 'text-primary-text' : 'text-error'"
      >
        {{ displayChange }}
      </span>
    </div>
  </div>
</template>

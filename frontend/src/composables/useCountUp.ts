import { ref, watch, onMounted } from 'vue';

/**
 * Composable que anima um número de 0 até o valor alvo.
 *
 * Utiliza requestAnimationFrame para interpolar suavemente
 * o valor numérico exibido, criando um efeito de "contagem"
 * em KPI cards e métricas do Dashboard.
 *
 * @param targetValue - Valor final a ser atingido
 * @param duration - Duração da animação em ms (padrão: 800)
 * @returns Ref reativa com o valor animado atual
 */
export function useCountUp(targetValue: () => number, duration = 800) {
  const current = ref(0);

  function animate(target: number) {
    const start = current.value;
    const diff = target - start;
    if (diff === 0) return;

    const startTime = performance.now();

    function step(now: number) {
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      /** Curva ease-out para desaceleração natural. */
      const eased = 1 - Math.pow(1 - progress, 3);
      current.value = start + diff * eased;

      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        current.value = target;
      }
    }

    requestAnimationFrame(step);
  }

  onMounted(() => animate(targetValue()));
  watch(targetValue, (val) => animate(val));

  return current;
}

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[1000] flex flex-col gap-2.5 pointer-events-none">
      <TransitionGroup
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-8 scale-95"
        enter-to-class="opacity-100 translate-x-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0 scale-100"
        leave-to-class="opacity-0 translate-x-8 scale-95"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-center gap-3 min-w-72 max-w-sm px-4 py-3 rounded-card bg-[var(--color-glass-bg)] backdrop-blur-xl border shadow-lg cursor-pointer group"
          :class="borderClass(toast.type)"
          @click="removeToast(toast.id)"
        >
          <!-- Reflexo de vidro no topo -->
          <div
            class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent to-transparent opacity-50 pointer-events-none rounded-t-card"
            :class="shineClass(toast.type)"
          ></div>

          <!-- Ícone -->
          <div
            class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
            :class="iconBgClass(toast.type)"
          >
            <CheckCircle v-if="toast.type === 'success'" :size="16" class="text-primary-text" />
            <XCircle v-else-if="toast.type === 'error'" :size="16" class="text-error" />
            <AlertTriangle v-else-if="toast.type === 'warning'" :size="16" class="text-warning" />
            <Info v-else :size="16" class="text-info" />
          </div>

          <!-- Mensagem -->
          <p class="flex-1 text-sm leading-snug">{{ toast.message }}</p>

          <!-- Botão fechar -->
          <button
            class="shrink-0 w-5 h-5 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition-opacity text-tertiary hover:text-primary-text cursor-pointer"
          >
            <X :size="12" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
  /**
   * Container global de notificações toast.
   *
   * Renderiza todos os toasts ativos com animação de slide-in
   * pela direita e glassmorphism consistente com o design system.
   * Teleportado para o body para ficar acima de tudo.
   * Clique no toast para descartá-lo manualmente.
   */
  import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';
  import { useToast } from '@/composables/useToast';

  const { toasts, removeToast } = useToast();

  /**
   * Retorna a classe de borda baseada no tipo do toast.
   */
  function borderClass(type: string): string {
    const map: Record<string, string> = {
      success: 'border-success-border',
      error: 'border-error-border',
      warning: 'border-warning-border',
      info: 'border-info-border',
    };
    return map[type] ?? map.info;
  }

  /**
   * Retorna a classe do brilho superior (glassmorphism shine).
   */
  function shineClass(type: string): string {
    const map: Record<string, string> = {
      success: 'via-primary/40',
      error: 'via-error/40',
      warning: 'via-warning/40',
      info: 'via-info/40',
    };
    return map[type] ?? map.info;
  }

  /**
   * Retorna a classe de fundo do ícone.
   */
  function iconBgClass(type: string): string {
    const map: Record<string, string> = {
      success: 'bg-success-bg',
      error: 'bg-error-bg',
      warning: 'bg-warning-bg',
      info: 'bg-info-bg',
    };
    return map[type] ?? map.info;
  }
</script>

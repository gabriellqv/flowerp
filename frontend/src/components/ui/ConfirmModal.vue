<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="visible"
        class="fixed inset-0 z-[999] flex items-center justify-center p-4"
        @click.self="cancel"
      >
        <!-- Overlay escurecido -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" />

        <!-- Painel glassmorphism -->
        <div
          class="relative w-full max-w-sm bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-6 shadow-2xl"
        >
          <!-- Reflexo de vidro no topo -->
          <div
            class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-60 pointer-events-none rounded-t-card"
          ></div>

          <!-- Ícone -->
          <div
            class="mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center"
            :class="
              variant === 'danger'
                ? 'bg-error/10 border border-error/20'
                : 'bg-warning-bg border border-warning-border'
            "
          >
            <AlertTriangle
              :size="22"
              :class="variant === 'danger' ? 'text-error' : 'text-warning'"
            />
          </div>

          <!-- Título -->
          <h3 class="text-center font-semibold text-lg mb-2">{{ title }}</h3>

          <!-- Mensagem -->
          <p class="text-center text-secondary text-sm leading-relaxed mb-6">
            {{ message }}
          </p>

          <!-- Ações -->
          <div class="flex items-center gap-3">
            <AppButton variant="secondary" class="flex-1" @click="cancel">
              {{ cancelText }}
            </AppButton>
            <AppButton
              :variant="variant === 'danger' ? 'danger' : 'primary'"
              class="flex-1"
              @click="confirm"
            >
              {{ confirmText }}
            </AppButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
  /**
   * Modal de confirmação com estilo glassmorphism.
   *
   * Substitui o `window.confirm()` nativo por um modal estilizado
   * que segue o design system do FlowERP. Suporta variantes
   * de perigo (exclusão) e alerta (ação irreversível).
   * Fecha ao clicar fora ou pressionar Escape.
   */
  import { onMounted, onUnmounted } from 'vue';
  import { AlertTriangle } from 'lucide-vue-next';
  import AppButton from '@/components/ui/AppButton.vue';

  interface ConfirmModalProps {
    visible: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning';
  }

  const props = withDefaults(defineProps<ConfirmModalProps>(), {
    title: 'Confirmar ação',
    message: 'Tem certeza que deseja continuar?',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    variant: 'danger',
  });

  const emit = defineEmits<{
    confirm: [];
    cancel: [];
  }>();

  function confirm() {
    emit('confirm');
  }

  function cancel() {
    emit('cancel');
  }

  function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.visible) {
      cancel();
    }
  }

  onMounted(() => {
    document.addEventListener('keydown', onKeydown);
  });

  onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown);
  });
</script>

<style scoped>
  /*
   * Animação de entrada e saída do modal.
   *
   * Overlay faz fade-in enquanto o painel escala de 95% a 100%,
   * criando efeito de "pop" suave e profissional.
   */
  .modal-enter-active,
  .modal-leave-active {
    transition: all 0.2s ease;
  }

  .modal-enter-from,
  .modal-leave-to {
    opacity: 0;
  }

  .modal-enter-from > div:last-child,
  .modal-leave-to > div:last-child {
    transform: scale(0.95);
  }
</style>

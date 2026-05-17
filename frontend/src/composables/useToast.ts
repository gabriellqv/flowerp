/**
 * Composable de notificações toast globais.
 *
 * Gerencia uma fila reativa de mensagens temporárias que são
 * exibidas no canto superior direito da tela. Suporta variantes
 * de sucesso, erro, aviso e info. Cada toast desaparece
 * automaticamente após a duração configurada (padrão 4s).
 *
 * @example
 * const { showToast } = useToast();
 * showToast('Produto salvo com sucesso!', 'success');
 */

import { ref } from 'vue';

export interface IToast {
  id: number;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
}

const toasts = ref<IToast[]>([]);
let nextId = 0;

/**
 * Composable para exibir notificações toast globais.
 *
 * @returns Estado reativo dos toasts e funções de controle.
 */
export function useToast() {
  /**
   * Exibe uma notificação toast temporária.
   *
   * @param message Texto da notificação.
   * @param type Variante visual (success, error, warning, info).
   * @param duration Tempo em ms antes de desaparecer (padrão 4000).
   */
  function showToast(message: string, type: IToast['type'] = 'success', duration = 4000) {
    const id = nextId++;
    toasts.value.push({ id, message, type });

    setTimeout(() => {
      removeToast(id);
    }, duration);
  }

  /**
   * Remove um toast da fila pelo ID.
   *
   * @param id Identificador único do toast.
   */
  function removeToast(id: number) {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  }

  return { toasts, showToast, removeToast };
}

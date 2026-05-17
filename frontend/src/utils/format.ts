/**
 * Utilitários de formatação compartilhados do FlowERP.
 *
 * Centraliza funções de formatação usadas por múltiplos componentes
 * para evitar duplicação e inconsistências visuais.
 */

/**
 * Formata valor numérico como moeda brasileira (R$).
 */
export function formatCurrency(value: number): string {
  return `R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

/**
 * Testes unitários dos utilitários de formatação.
 *
 * Valida a função formatCurrency em cenários reais:
 * inteiros, centavos, zero, valores altos e negativos.
 */

import { describe, it, expect } from 'vitest';
import { formatCurrency } from '@/utils/format';

describe('formatCurrency', () => {
  it('formata valor inteiro', () => {
    expect(formatCurrency(100)).toBe('R$ 100,00');
  });

  it('formata valor com centavos', () => {
    expect(formatCurrency(99.9)).toBe('R$ 99,90');
  });

  it('formata zero', () => {
    expect(formatCurrency(0)).toBe('R$ 0,00');
  });

  it('formata valor com separador de milhar', () => {
    expect(formatCurrency(1500000.5)).toBe('R$ 1.500.000,50');
  });

  it('formata valor pequeno com centavos', () => {
    expect(formatCurrency(0.99)).toBe('R$ 0,99');
  });

  it('formata valor negativo', () => {
    const result = formatCurrency(-150.5);
    expect(result).toContain('150,50');
  });

  it('arredonda para duas casas decimais', () => {
    expect(formatCurrency(10.999)).toBe('R$ 11,00');
  });

  it('formata valores de ticket medio', () => {
    expect(formatCurrency(247.5)).toBe('R$ 247,50');
  });
});

/**
 * Composable de controle de tema (claro/escuro).
 *
 * @description
 * Encapsula `useDark` do @vueuse/core com configuracao via
 * atributo `data-theme` no <html>. Persiste preferencia
 * em localStorage com fallback para tema escuro.
 */

import { useDark, useToggle } from '@vueuse/core';

/**
 * Se o tema atual e escuro.
 * Vinculado ao atributo `data-theme="dark"` no <html>.
 */
export const isDark = useDark({
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
  initialValue: 'dark',
});

/**
 * Alterna entre tema claro e escuro.
 */
export const toggleTheme = useToggle(isDark);

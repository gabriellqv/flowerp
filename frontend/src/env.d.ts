/**
 * Declarações de tipo para o ambiente Vite.
 *
 * @description Habilita o suporte do TypeScript para módulos `.vue`
 * (Single File Components) e variáveis de ambiente injetadas pelo Vite.
 * @see {@link https://vitejs.dev/guide/env-and-mode.html}
 */
/// <reference types="vite/client" />

/**
 * Declaração de módulo para componentes Vue SFC (`.vue`).
 *
 * Permite que o TypeScript resolva imports de arquivos `.vue`
 * sem erros de tipagem, retornando o tipo genérico `DefineComponent`.
 */
declare module '*.vue' {
  import type { DefineComponent } from 'vue';

  const component: DefineComponent<object, object, unknown>;
  export default component;
}

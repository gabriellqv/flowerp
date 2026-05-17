/**
 * Configuração do Vite para o frontend FlowERP.
 *
 * @description Define plugins (Vue + Tailwind CSS + Vue DevTools), alias
 * de caminhos (`@/`) e proxy reverso para redirecionar chamadas `/api`
 * ao backend Laravel durante o desenvolvimento, evitando problemas de CORS.
 * Inclui configuração do Vitest com ambiente jsdom para testes unitários.
 *
 * @see {@link https://vitejs.dev/config/}
 */

/// <reference types="vitest/config" />
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import vueDevTools from 'vite-plugin-vue-devtools';
import { fileURLToPath } from 'node:url';

export default defineConfig({
  plugins: [vue(), tailwindcss(), vueDevTools()],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
  },
  server: {
    port: 3000,
    proxy: {
      '/api': 'http://localhost:8000',
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
  },
});

/**
 * Configuracao do ESLint para o frontend FlowERP.
 *
 * @description Utiliza flat config com regras recomendadas para
 * TypeScript, Vue 3 (SFC) e integracao com Prettier para evitar
 * conflitos de formatacao.
 *
 * @see {@link https://eslint.org/docs/latest/use/configure/configuration-files-new}
 */
import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import pluginVue from 'eslint-plugin-vue';
import eslintConfigPrettier from 'eslint-config-prettier';

export default tseslint.config(
  {
    ignores: ['dist/**', 'coverage/**', 'node_modules/**'],
  },
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  eslintConfigPrettier,
  {
    files: ['**/*.vue'],
    languageOptions: {
      parserOptions: {
        parser: tseslint.parser,
      },
    },
  },
  {
    files: ['**/*.vue', '**/*.ts'],
    languageOptions: {
      globals: {
        clearTimeout: 'readonly',
        setTimeout: 'readonly',
        requestAnimationFrame: 'readonly',
        performance: 'readonly',
        AbortController: 'readonly',
        HTMLElement: 'readonly',
        HTMLInputElement: 'readonly',
        KeyboardEvent: 'readonly',
        Event: 'readonly',
        document: 'readonly',
        window: 'readonly',
        console: 'readonly',
      },
    },
  },
  {
    rules: {
      'vue/multi-word-component-names': 'off',
      '@typescript-eslint/no-explicit-any': 'error',
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      'no-console': ['warn', { allow: ['warn', 'error'] }],
    },
  },
);

/**
 * Ponto de entrada da aplicação FlowERP (frontend).
 *
 * @description Inicializa a instância Vue e monta o componente raiz
 * no elemento `#app` do DOM. Importa os estilos globais do Tailwind CSS.
 */
import { createApp } from 'vue';
import App from './App.vue';
import './style.css';

const app = createApp(App);
app.mount('#app');

/**
 * Entry point da aplicacao Vue.
 *
 * Inicializa Pinia (gerenciamento de estado) e Vue Router
 * (navegacao SPA) antes de montar a arvore de componentes.
 */

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import './style.css';

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');

/**
 * Store de autenticacao com state reativo e persistencia local.
 *
 * Gerencia login, logout, restauracao de sessao (fetchMe) e
 * expoe computeds de permissao (isAdmin, canSell) para uso em
 * guards de rota e controles condicionais na UI.
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { IUser, ILoginResponse } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'));
  const user = ref<IUser | null>(null);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isManager = computed(() => user.value?.role === 'manager');
  const canSell = computed(() => ['admin', 'manager', 'seller'].includes(user.value?.role ?? ''));

  async function login(email: string, password: string) {
    const { data } = await api.post<ILoginResponse>('/auth/login', { email, password });
    token.value = data.access_token;
    user.value = data.user;
    localStorage.setItem('token', data.access_token);
  }

  async function fetchMe() {
    try {
      const { data } = await api.get<IUser>('/auth/me');
      user.value = data;
    } catch {
      logout();
    }
  }

  function logout() {
    try {
      api.post('/auth/logout');
    } catch {
      /* ignora erro offline */
    }
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
  }

  return { token, user, isAuthenticated, isAdmin, isManager, canSell, login, logout, fetchMe };
});

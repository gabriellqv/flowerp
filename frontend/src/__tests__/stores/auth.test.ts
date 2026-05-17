/**
 * Testes unitários da store de autenticação (Pinia).
 *
 * Valida o estado inicial, computeds de permissão por role,
 * e o fluxo de logout (limpeza de token e usuario).
 * Usa mock do axios para isolar da API real.
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import type { IUser } from '@/types';

// Mock do modulo api para evitar chamadas HTTP reais
vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
  },
}));

/** Cria um usuario fake para testes de permissao. */
function createFakeUser(role: IUser['role']): IUser {
  return {
    id: 'uuid-test-001',
    name: 'Teste',
    email: 'teste@flowerp.com',
    role,
  };
}

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    localStorage.clear();
  });

  // ==========================================
  // Estado inicial
  // ==========================================

  it('comeca sem autenticacao', () => {
    const auth = useAuthStore();

    expect(auth.isAuthenticated).toBe(false);
    expect(auth.user).toBeNull();
    expect(auth.token).toBeNull();
  });

  it('restaura token do localStorage se existir', () => {
    localStorage.setItem('token', 'token-persistido');
    setActivePinia(createPinia());

    const auth = useAuthStore();
    expect(auth.token).toBe('token-persistido');
    expect(auth.isAuthenticated).toBe(true);
  });

  // ==========================================
  // Computeds de permissao
  // ==========================================

  it('isAdmin retorna true para admin', () => {
    const auth = useAuthStore();
    auth.user = createFakeUser('admin');

    expect(auth.isAdmin).toBe(true);
    expect(auth.isManager).toBe(false);
    expect(auth.canSell).toBe(true);
  });

  it('isManager retorna true para manager', () => {
    const auth = useAuthStore();
    auth.user = createFakeUser('manager');

    expect(auth.isAdmin).toBe(false);
    expect(auth.isManager).toBe(true);
    expect(auth.canSell).toBe(true);
  });

  it('canSell retorna true para seller', () => {
    const auth = useAuthStore();
    auth.user = createFakeUser('seller');

    expect(auth.isAdmin).toBe(false);
    expect(auth.isManager).toBe(false);
    expect(auth.canSell).toBe(true);
  });

  it('viewer nao tem nenhuma permissao elevada', () => {
    const auth = useAuthStore();
    auth.user = createFakeUser('viewer');

    expect(auth.isAdmin).toBe(false);
    expect(auth.isManager).toBe(false);
    expect(auth.canSell).toBe(false);
  });

  it('permissoes sao false sem usuario logado', () => {
    const auth = useAuthStore();

    expect(auth.isAdmin).toBe(false);
    expect(auth.isManager).toBe(false);
    expect(auth.canSell).toBe(false);
  });

  // ==========================================
  // Logout
  // ==========================================

  it('logout limpa token, usuario e localStorage', () => {
    const auth = useAuthStore();
    auth.token = 'fake-token';
    auth.user = createFakeUser('admin');
    localStorage.setItem('token', 'fake-token');

    auth.logout();

    expect(auth.token).toBeNull();
    expect(auth.user).toBeNull();
    expect(auth.isAuthenticated).toBe(false);
    expect(localStorage.getItem('token')).toBeNull();
  });
});

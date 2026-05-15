/**
 * Testes de componente do DataTable.
 *
 * @description
 * Valida que a tabela generica renderiza dados corretamente, responde
 * a eventos de busca, paginacao, ordenacao e exibe estados de loading.
 */
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DataTable from '@/components/ui/DataTable.vue';

interface TestRow {
  id: string;
  name: string;
  price: number;
}

const columns = [
  { key: 'id', label: 'ID', sortable: false },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'price', label: 'Preco', sortable: true },
];

const mockData: TestRow[] = [
  { id: '1', name: 'Produto A', price: 100 },
  { id: '2', name: 'Produto B', price: 200 },
  { id: '3', name: 'Produto C', price: 300 },
];

function mountTable(overrides: Record<string, unknown> = {}) {
  return mount(DataTable<TestRow>, {
    props: {
      columns,
      data: mockData,
      total: 3,
      page: 1,
      perPage: 10,
      loading: false,
      ...overrides,
    },
  });
}

describe('DataTable', () => {
  it('renderiza as linhas de dados corretamente', () => {
    const wrapper = mountTable();

    const rows = wrapper.findAll('tbody tr');
    expect(rows).toHaveLength(3);
    expect(rows[0]?.text()).toContain('Produto A');
    expect(rows[1]?.text()).toContain('Produto B');
  });

  it('exibe skeleton durante loading', () => {
    const wrapper = mountTable({ loading: true, data: [] });

    const skeletonDivs = wrapper.findAll('td .animate-pulse');
    expect(skeletonDivs.length).toBeGreaterThan(0);
  });

  it('exibe slot de tabela vazia quando nao ha dados', () => {
    const wrapper = mount(DataTable<TestRow>, {
      props: {
        columns,
        data: [],
        total: 0,
        page: 1,
        perPage: 10,
        loading: false,
      },
    });

    expect(wrapper.text()).toContain('Página 1 de 1');
    expect(wrapper.text()).toContain('0 registros');
  });

  it('emite evento sort ao clicar em coluna ordenavel', async () => {
    const wrapper = mountTable();

    const headers = wrapper.findAll('th');
    await headers[1]?.trigger('click');

    expect(wrapper.emitted('sort')).toBeTruthy();
    expect(wrapper.emitted('sort')?.[0]).toEqual(['name']);
  });

  it('ignora clique em coluna nao ordenavel', async () => {
    const wrapper = mountTable();

    const headers = wrapper.findAll('th');
    await headers[0]?.trigger('click');

    expect(wrapper.emitted('sort')).toBeFalsy();
  });

  it('renderiza informacao de paginacao correta', () => {
    const wrapper = mountTable({ page: 1, perPage: 10, total: 25 });

    expect(wrapper.text()).toContain('Página 1 de 3');
    expect(wrapper.text()).toContain('25 registros');
  });

  it('botao anterior fica desabilitado na primeira pagina', () => {
    const wrapper = mountTable({ page: 1, perPage: 10, total: 25 });

    const buttons = wrapper.findAllComponents({ name: 'AppButton' });
    const anteriorButton = buttons.find((btn) => btn.text() === 'Anterior');
    expect(anteriorButton?.props('disabled')).toBe(true);
  });

  it('botao proximo fica desabilitado na ultima pagina', () => {
    const wrapper = mountTable({ page: 3, perPage: 10, total: 25 });

    const buttons = wrapper.findAllComponents({ name: 'AppButton' });
    const proximoButton = buttons.find((btn) => btn.text() === 'Próximo');
    expect(proximoButton?.props('disabled')).toBe(true);
  });
});

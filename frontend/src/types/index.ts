/**
 * Tipos e interfaces compartilhados do frontend FlowERP.
 *
 * Define os contratos de dados entre a API (Laravel) e os componentes Vue,
 * garantindo type safety em toda a camada de apresentacao.
 *
 * Todas as interfaces seguem o padrao PascalCase com prefixo `I`
 * conforme definido nas convencoes do projeto (AGENTS.md).
 */

export interface IUser {
  id: string;
  name: string;
  email: string;
  role: 'admin' | 'manager' | 'seller' | 'viewer';
}

export interface ILoginResponse {
  access_token: string;
  user: IUser;
}

export interface ICategory {
  id: string;
  name: string;
}

export interface ICustomer {
  id: string;
  name: string;
  email: string;
  phone: string;
  document: string;
  is_active: boolean;
}

export interface IProduct {
  id: string;
  name: string;
  sku: string;
  category_id: string;
  category?: ICategory;
  cost_price: number;
  sale_price: number;
  stock_quantity: number;
  min_stock: number;
  is_active: boolean;
}

export interface IPaginatedResponse<T> {
  data: T[];
  total: number;
  page?: number;
  per_page?: number;
  last_page?: number;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ISaleItem {
  product_id: string;
  product?: IProduct;
  quantity: number;
  unit_price: number;
}

export interface ISale {
  id: string;
  seller: IUser;
  customer?: { id: string; name: string };
  items: ISaleItem[];
  total_amount: number;
  discount: number;
  payment_method: string | null;
  status: string;
  created_at: string;
}

export interface IDashboardSummary {
  active_products: number;
  monthly_revenue: number;
  previous_revenue: number;
  revenue_change: number | null;
  low_stock_count: number;
  zero_stock_count: number;
  monthly_sales_count: number;
  previous_sales_count: number;
  sales_change: number | null;
  average_ticket: number;
  total_stock_value: number;
}

/**
 * Ponto de dado para gráficos de receita.
 */
export interface IChartDataPoint {
  label: string;
  value: number;
}

/**
 * Registro de atividade do sistema para o feed do dashboard.
 */
export interface IActivityLogEntry {
  id: string;
  user_id: number;
  user: { id: number; name: string } | null;
  action: string;
  entity: string;
  details: Record<string, unknown> | null;
  created_at: string;
}

export interface ICartItem {
  product: IProduct;
  quantity: number;
}

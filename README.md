# FlowerERP

Sistema de gestão empresarial moderno, rápido e escalável.
Projeto desenvolvido como monorepo contendo um backend em Laravel (API REST) e um frontend em Vue 3 (Composition API).

## Tecnologias

- **Backend:** Laravel 11+, PHP 8.2+, SQLite, Sanctum (Autenticação)
- **Frontend:** Vue 3, TypeScript, Vite, Tailwind CSS, Pinia, Vue Router
- **Qualidade:** ESLint, Prettier, Laravel Pint

## Estrutura do Projeto

O projeto adota uma arquitetura de monorepo dividida em duas aplicações principais:

- `/backend` - API RESTful robusta desenvolvida em Laravel.
- `/frontend` - SPA (Single Page Application) moderna em Vue 3.

## Como Executar Localmente

### 1. Requisitos

- PHP 8.2+ e Composer
- Node.js 20+ e NPM
- Git

### 2. Backend (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```
A API estará disponível em `http://localhost:8000`.

### 3. Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```
A interface estará disponível em `http://localhost:3000`.

## Usuários de Teste

Após rodar as migrations com seed (`--seed`), você pode utilizar as seguintes credenciais:

| Perfil | E-mail | Senha |
|---|---|---|
| Admin | `admin@flowerp.com` | `senha123` |
| Gerente | `gerente@flowerp.com` | `senha123` |

## Padrões e Convenções

Este projeto segue regras estritas de padronização. Para detalhes completos, qualquer colaborador (humano ou IA) deve consultar as diretrizes estabelecidas na arquitetura do projeto (como uso de UUIDs, regras de nomenclatura e formatação TSDoc/PHPDoc).

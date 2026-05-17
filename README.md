# FlowERP

Sistema de gestao empresarial moderno, rapido e escalavel.
Projeto desenvolvido como monorepo contendo um backend em Laravel (API REST) e um frontend em Vue 3 (Composition API).

## Tecnologias

- **Backend:** Laravel 13, PHP 8.3, Sanctum (Autenticacao)
- **Banco de dados:** SQLite (desenvolvimento local e testes) / MySQL 8 (Docker)
- **Frontend:** Vue 3, TypeScript, Vite, Tailwind CSS 4, Pinia, Vue Router
- **Qualidade:** ESLint, Prettier, Laravel Pint, Pest PHP, Vitest
- **DevOps:** Docker, Docker Compose, GitHub Actions (CI)

## Estrutura do Projeto

O projeto adota uma arquitetura de monorepo dividida em duas aplicacoes principais:

- `/backend` - API RESTful robusta desenvolvida em Laravel.
- `/frontend` - SPA (Single Page Application) moderna em Vue 3.

## Como Executar Localmente

### Com Docker (recomendado)

```bash
docker compose up --build -d
```

- Frontend: http://localhost:3000
- API: http://localhost:8000

### Manual

#### 1. Requisitos

- PHP 8.3+ e Composer
- Node.js 20+ e NPM
- Git

#### 2. Backend (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```
A API estara disponivel em `http://localhost:8000`.

#### 3. Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```
A interface estara disponivel em `http://localhost:3000`.

## Testes

```bash
# Backend
cd backend
php artisan test

# Frontend
cd frontend
npm run test
```

## Usuarios de Teste

Apos rodar as migrations com seed (`--seed`), voce pode utilizar as seguintes credenciais:

| Perfil | E-mail | Senha |
|---|---|---|
| Admin | `admin@flowerp.com` | `senha123` |
| Gerente | `gerente@flowerp.com` | `senha123` |

## Padroes e Convencoes

Este projeto segue regras estritas de padronizacao. Para detalhes completos, qualquer colaborador (humano ou IA) deve consultar as diretrizes estabelecidas na arquitetura do projeto (como uso de UUIDs, regras de nomenclatura e formatacao TSDoc/PHPDoc).

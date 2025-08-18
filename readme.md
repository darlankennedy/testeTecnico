# Application Monorepo — Laravel 12 (JWT) + Vue 3

Este repositório contém uma aplicação em estrutura **monorepo** com os diretórios:

```
application/
  backend/   ← API em Laravel 12 com JWT
  frontend/  ← SPA em Vue 3 (Pinia + Vuetify)
```

---

## 📦 Stacks

**Backend**
- Laravel **12**
- Autenticação **JWT** (`php-open-source-saver/jwt-auth`)
- PostgreSQL
- Migrations + Seeds
- Testes (PHPUnit/Pest)
- Docker (PHP-FPM + Apache + Postgres)
- Pasta `doc/sql` com consultas SQL úteis

**Frontend**
- Vue **^3.5.18**
- Pinia (store)
- Vuetify (UI)
- Vite

---

## 🔗 Endereços (ambiente de desenvolvimento)

**Frontend (Vite):**
- Local: **http://localhost:5173/**
- Network (Docker): **http://172.18.0.5:5173/** *(acessível entre containers)*
- Vue DevTools: **http://localhost:5173/__devtools__/**

**Backend (Apache → PHP-FPM):**
- API (HTTP): **http://localhost:3543/api/v1**
- API (HTTPS): **https://localhost:3544/api/v1** *(se o serviço 443 estiver ativo)*

> Dica: no **frontend** use `VITE_API_URL=http://localhost:3543/api/v1` para apontar para a API acima.

---

## 🗂️ Estrutura de pastas (resumo)

```
application/
  backend/
    app/
    bootstrap/
    config/
    database/
      migrations/
      seeders/
    doc/
      sql/            ← consultas SQL utilizadas no projeto
    public/
    routes/
    tests/
    .env.example
    Dockerfile
    docker-compose.yml (ou na raiz, conforme seu setup)
  frontend/
    src/
    public/
    .env
    .env.development
    package.json
    vite.config.ts
```

---

## 🧩 Modelagem (ERD)

![ERD](docs/erd.png)

Relação: **users (1) — (N) products**.

---

## ⚙️ Configuração — Backend (Laravel 12)

1. **.env**
    - Copie o arquivo de exemplo e ajuste as variáveis de ambiente:
   ```bash
   cp application/backend/.env.example application/backend/.env
   ```

   **Exemplo mínimo (.env):**
   ```env
   APP_NAME=Laravel
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   LOG_CHANNEL=stack
   LOG_LEVEL=debug

   DB_CONNECTION=pgsql
   DB_HOST=postgres
   DB_PORT=5432
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=secret

   # JWT
   JWT_SECRET=
   JWT_TTL=60
   JWT_REFRESH_TTL=20160
   ```

2. **Geração de chave e migrações**
   ```bash
   # dentro do container ou localmente se tiver PHP/Composer
   php artisan key:generate
   php artisan migrate --seed
   php artisan jwt:secret
   ```

3. **Nginx/Apache — Header Authorization**
   > Necessário para qualquer autenticação via `Authorization: Bearer` chegar ao PHP.

   Apache:
   ```apache
   SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
   ```

   Nginx (se usar Nginx no lugar do Apache):
   ```nginx
   fastcgi_param HTTP_AUTHORIZATION $http_authorization;
   ```

5. **Rotas protegidas (exemplo)**
   ```php
   // routes/api.php
   Route::prefix('v1')->group(function () {
       Route::post('/login', [AuthController::class, 'login']);
       Route::post('/register', [AuthController::class, 'register']);

       Route::middleware('auth:api')->group(function () {
           Route::get('/me', [AuthController::class, 'me']);
           Route::post('/logout', [AuthController::class, 'logout']);
           Route::post('/refresh', [AuthController::class, 'refresh']);
       });
   });
   ```

---

## 🐳 Subir com Docker (stack completa)

No diretório onde está seu `docker-compose.yml`:

```bash
docker compose build
docker compose up -d

# Executar comandos do Laravel no container "app"
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan jwt:secret
```

A API deverá responder em: **http://localhost:3543/api/v1**.

---

## ▶️ Rodar separado (sem Docker Compose)

### Backend
```bash
cd application/backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan jwt:secret
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend
```bash
cd application/frontend
cp .env.development .env
npm i
npm run dev
```

---

## 🌐 Configuração — Frontend (Vite/Vue)

Arquivos de ambiente:

**`application/frontend/.env.development`**
```env
VITE_API_URL=http://localhost:3543/api/v1
VITE_BACKEND_URL=http://127.0.0.1:8000
VITE_APP_NAME="Meu App (Dev)"
```

**`application/frontend/.env` (produção — exemplo)**
```env
# Corrigido: use /api/v1 em vez de ":api/v1"
VITE_API_URL=https://api.seudominio.com/api/v1
VITE_APP_NAME="Meu App"
```

> Observação: no snippet anterior havia `http://localhost:3543:api/v1` (com dois `:`). O correto é `http://localhost:3543/api/v1`.

---

## 🔐 Fluxo de Autenticação (JWT)

1. `POST /api/v1/login` → retorna `{ access_token, token_type, expires_in, user }`
2. Envie o header em todas as rotas protegidas:
   ```
   Authorization: Bearer <access_token>
   Accept: application/json
   ```
3. `GET /api/v1/me` → dados do usuário autenticado
4. `POST /api/v1/refresh` → novo token
5. `POST /api/v1/logout` → invalida o token

---

## 📚 Documentação da API (opcional — Dedoc/Scramble)

- UI: `http://localhost:3543/docs` (ou `http://127.0.0.1:8000/docs` se rodando sem Docker)
- OpenAPI JSON: `http://localhost:3543/docs/openapi.json`

Para habilitar localmente:
```bash
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag="config"
```
`.env`:
```env
SCRAMBLE_ENABLED=true
```

---

## 🧪 Testes

### Backend
- PHPUnit/Pest:
  ```bash
  cd application/backend
  php artisan test
  # ou
  ./vendor/bin/pest
  ```

- Exemplos a cobrir:
    - Auth (login/refresh/logout/me)
    - Repositórios (paginate, filtros, buscas)
    - Services (regras de negócio)
    - Controllers (HTTP 200/401/422 etc.)

### Frontend
```bash
cd application/frontend
npm run test
```
> Configure Vitest/Jest de acordo com sua stack.

---

## 🗃️ SQL úteis

- Consulte `application/backend/doc/sql/` para queries pré-montadas (ex.: agregações de usuários e produtos).
- Para rodar rapidamente:
  ```bash
  docker compose exec postgres psql -U laravel -d laravel -f /var/www/html/doc/sql/sua_query.sql
  ```
  > Ajuste o caminho conforme montagem do volume no compose.

---

## 🔧 Troubleshooting

- **401 Unauthenticated**: verifique se o header `Authorization` chega ao PHP (Apache/Nginx), se o guard `api` está com driver `jwt`, se o token não expirou.
- **CORS**: libere `allowed_headers` e inclua o origin do frontend.
- **URL errada**: confirme `VITE_API_URL` e portas do Docker.
- **Migrations duplicadas**: se não usa mais Sanctum, remova o pacote/migrations/tabela `personal_access_tokens`.

---

## 📄 Licença
Defina aqui a licença do projeto (MIT, Proprietary, etc.).

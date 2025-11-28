# Sincro API

API para gerenciamento de finanças pessoais e em grupo.

## 🚀 Quickstart

```bash
# Clone o repositório
git clone https://github.com/Vinicius-b-oliveira/sincro-api.git
cd sincro-api

# Copie e configure o .env
cp .env.example .env
```

Edite o `.env` com as configurações do Sail:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=sincro
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

```bash
# Instale as dependências
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs

# Suba os containers
./vendor/bin/sail up -d

# Gere a chave e rode as migrations
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# (Opcional) Rode os seeders
./vendor/bin/sail artisan db:seed
```

A API estará em: http://localhost

# EuroSocietes

Repertoire europeen des societes — plateforme d'information sur les societes europeennes, leurs dirigeants et leurs etablissements.

## Structure

```
├── backend/   Laravel 12 API (PHP 8.4, PostgreSQL, Redis, Sanctum, RBAC)
└── .opencode/ Local agent configuration and skills
```

See [backend/README.md](backend/README.md) for setup, API documentation, and testing instructions.

## Quick start

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

API: `http://localhost:8000`

## Install as prebuilt image

Everything (Nginx + PHP-FPM + Laravel API) is baked into one image; Postgres and Redis run alongside. No PHP, Composer, or Node needed.

```bash
docker compose -f docker-compose.prodhow .yml up -d
```

- API: `http://localhost:8000`
- Postgres: `localhost:5432`, Redis: `localhost:6379`
- On first boot it runs migrations + seeds (admin/user accounts).
- Run tests: `docker compose -f docker-compose.prod.yml exec app php artisan test`
- Config lives in `docker-compose.prod.yml`; set `APP_KEY` in your shell to keep tokens stable across restarts.

## Status

Phase 1 (foundation) — API authentication, RBAC, structured error handling and logging. Further phases (company management, content, frontend) to follow.

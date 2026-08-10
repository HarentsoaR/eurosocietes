# EuroSocietes — Backend API

Laravel 12 API for the EuroSocietes platform (repertoire européen des sociétés).

## Stack

- **Laravel 12** (PHP 8.4)
- **PostgreSQL** (database)
- **Redis** (cache / queue / session)
- **Laravel Sanctum** (token authentication)
- **spatie/laravel-permission** (RBAC)
- **Docker Compose** (local environment: `app`, `postgres`, `redis`)

## Quick start

```bash
# from the repository root
docker compose up -d --build
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

The API listens on `http://localhost:8000`.

## Architecture

| Concern | Location |
| --- | --- |
| API routes | `routes/api.php` (prefix `api/v1`) |
| Controllers | `app/Http/Controllers/Api/` |
| Form requests (validation) | `app/Http/Requests/Api/` |
| JSON resources | `app/Http/Resources/` |
| Roles / permissions enums | `app/Enums/Role.php`, `app/Enums/Permission.php` |
| Roles & permissions seeder | `database/seeders/RolePermissionSeeder.php` |
| Error handling (JSON) | `app/Exceptions/Handler.php` |
| Request logging context | `app/Http/Middleware/RequestContext.php` |

## Authentication

Sanctum personal access tokens. All protected routes require an `Authorization: Bearer <token>` header.

| Method | Route | Description |
| --- | --- | --- |
| `POST` | `/api/v1/register` | Create account (default role: `utilisateur`) — throttled 10/min |
| `POST` | `/api/v1/login` | Issue a token (`device_name` optional) — throttled 5/min |
| `POST` | `/api/v1/logout` | Revoke the current token |
| `GET` | `/api/v1/me` | Current user profile |
| `POST` | `/api/v1/password/forgot` | Send reset link — throttled 5/min |
| `POST` | `/api/v1/password/reset` | Reset password with token |

### Example

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret"}' | jq -r .data.token)

curl -s http://localhost:8000/api/v1/me -H "Authorization: Bearer $TOKEN"
```

## RBAC

Four roles are defined in `app/Enums/Role.php` and seeded via `RolePermissionSeeder`:

| Role (key) | Label | Permissions |
| --- | --- | --- |
| `admin` | Admin | All |
| `editeur` | Éditeur | companies.view, companies.update, content.* |
| `entreprise` | Entreprise | companies.view, content.view, profile.* |
| `utilisateur` | Utilisateur | companies.view, content.view |

Permissions follow a `{resource}.{action}` convention and are enumerated in `app/Enums/Permission.php`. Routes are protected with the `role:` middleware, e.g. `role:admin`.

## Error handling

API requests receive consistent JSON errors:

```json
{
  "message": "Route introuvable.",
  "errors": { "email": ["..."] }
}
```

- `404` unknown API routes, `401` unauthenticated, `403` unauthorized role, `422` validation.
- Internal error details and traces are only returned when `APP_DEBUG=true`.
- Every response carries an `X-Request-ID` header (client-provided IDs are echoed).
- Logs carry request context (`request_id`, `url`, `method`, `user_id`).

## Logging

Default: daily files in `storage/logs/laravel.log`. For machine-parseable output set `LOG_CHANNEL=json` (writes `storage/logs/laravel-json.log`) for aggregators like Loki, ELK, or Datadog.

## Testing

```bash
docker compose exec app php artisan test
```

Tests run against the `eurosocietes_test` PostgreSQL database (see `phpunit.xml`). `RefreshDatabase` is used; the role/permission seeder runs via the `SeedsRoles` trait.

## Configuration

Copy `backend/.env.example` to `backend/.env` and adjust. Required services are validated at boot (see `app/Support/EnvironmentValidator.php`); the app refuses to start when critical values are missing.

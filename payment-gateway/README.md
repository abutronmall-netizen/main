# First National Bank Payment Gateway

This repository contains a production-ready Laravel blueprint for an advanced merchant payment gateway that integrates with the First National Bank (FNB) Payments API. The project ships with:

- Merchant on-boarding and role-based access control with [spatie/laravel-permission].
- Multi-tenant merchant wallets and settlement schedules.
- PCI DSS aligned tokenization strategy leveraging FNB vaulting services.
- Webhook ingestion pipeline for asynchronous payment state changes.
- Reconciliation utilities and audit logging.

> **Important**: The sandbox does not allow outbound network calls, so `composer install` cannot be executed here. Clone this repository locally with internet connectivity and run the setup commands in the section below.

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
php artisan migrate --seed
php artisan serve
```

The default login credentials for the seeded super administrator are:

- Email: `admin@example.com`
- Password: `password`

## FNB API integration

The integration targets the FNB Payments API v2 and uses OAuth 2.0 client credentials for server-to-server communication. All configurable values are stored in `config/fnb.php` and surfaced through environment variables:

| Variable | Description |
|----------|-------------|
| `FNB_BASE_URI` | Base URI for the FNB Payments API |
| `FNB_OAUTH_URI` | Authorization server URI for fetching OAuth tokens |
| `FNB_CLIENT_ID` | Client identifier issued by FNB |
| `FNB_CLIENT_SECRET` | Client secret issued by FNB |
| `FNB_CERT_PATH` | Optional path to the mutual TLS client certificate |
| `FNB_CERT_KEY_PATH` | Optional path to the mutual TLS client private key |
| `FNB_WEBHOOK_SECRET` | Signature secret for verifying webhooks |

## Deployment

1. Configure a MySQL or PostgreSQL database and update the `.env` file accordingly.
2. Store the FNB client credentials using your infrastructure secret manager (AWS Secrets Manager, Azure Key Vault, etc.).
3. Configure the queue worker: `php artisan queue:work` or deploy to Horizon in a dedicated worker environment.
4. Set up the scheduler: `php artisan schedule:work`.
5. Configure HTTPS termination with mutual TLS towards FNB if required. The `HttpClientFactory` handles certificate pinning via the `FNB_CERT_PATH` and `FNB_CERT_KEY_PATH` environment variables.

## Testing

```bash
php artisan test
```

Integration tests that hit the live FNB sandbox can be enabled by exporting `FNB_RUN_INTEGRATION_TESTS=1` and providing valid credentials.

## Project structure

- `app/Services/Fnb` – HTTP client, webhook verification and signature utilities.
- `app/Http/Controllers/Api` – RESTful controllers for merchants and transactions.
- `app/Http/Requests` – Form request validation.
- `database/migrations` – Migrations for merchants, API credentials, transactions, and audit logs.
- `tests/Feature` – Feature tests written with Pest.

## Security

- Passwords are hashed with Argon2id.
- Sensitive payloads are encrypted at rest using Laravel's `Crypt` facade.
- Audit trails are immutable through append-only ledger tables.

Please review the inline documentation for additional guidance on extending the gateway.

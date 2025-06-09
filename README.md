# Ainstein

Ainstein is an AI‑assisted content platform built with Laravel. It uses Jetstream, Livewire and the stancl/tenancy package to provide a multi‑tenant environment. The platform can integrate with OpenAI and SerpAPI to generate articles and other content.

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm

## Installation

1. Clone the repository and install dependencies:
   ```bash
   composer install
   npm install
   ```
2. Copy `.env.example` to `.env` and set the application key:
   ```bash
   php artisan key:generate
   ```
3. Configure the following environment variables in `.env`:
   - `APP_URL` – base URL for your instance
   - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `SERP_API_KEY` – key for SerpAPI
   - `OPENAI_API_KEY` – key for the OpenAI client
   - any mail or queue settings required by your environment
4. Run the database migrations:
   ```bash
   php artisan migrate
   ```

### Multitenancy

Central domains are defined in `config/tenancy.php`. After configuring them you can create tenants and run their migrations:

```bash
php artisan tenants:create example
php artisan tenants:migrate
```

Each tenant is accessible via its domain or subdomain (for example `example.localhost`).

### Local development

Start the application with the included development script which runs the server, queue listener and Vite:

```bash
composer run dev
```

### Tests

Execute the automated test suite with:

```bash
composer test
```

## Contributing

Pull requests are welcome. Please follow PSR‑12 style, add tests for new features and ensure `composer test` passes before submitting.

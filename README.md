# StockDivs

StockDivs is a Laravel + React application for tracking stock quotes, portfolio performance, dividends, watchlists, alerts, and currency conversion.

## Architecture

Backend responsibilities:

- Laravel REST API protected with Sanctum bearer tokens.
- Eloquent models for users, stocks, quotes, portfolio transactions, aggregate positions, dividends, watchlists, alerts, exchange rates, and user settings.
- Service layer for market data, quotes, dividends, portfolio calculations, exchange rates, and alert checks.
- Scheduler-ready Artisan commands for quote refreshes, dividend refreshes, exchange-rate updates, and alert checks.
- Replaceable market-data provider via `MarketDataProviderInterface`.

Frontend responsibilities:

- React SPA rendered through Vite and Laravel Blade fallback routing.
- React Router pages for auth, dashboard, portfolio, position details, stock search, watchlist, dividends, alerts, and settings.
- Fetch API client with Sanctum bearer token storage.
- Recharts visualizations for portfolio value, allocation, and currency exposure.
- Interface language switching for English, Russian, and German.
- Responsive CSS with light/dark-ready design tokens.

## Project Structure

```text
app/
  Console/Commands/          Scheduler command entry points
  Http/Controllers/Api/      REST controllers
  Http/Requests/             Form validation
  Http/Resources/            API serializers
  Models/                    Eloquent models
  Services/                  Business services
  Services/MarketData/       Provider interface and adapters
database/
  migrations/                Schema definitions
  seeders/                   Demo portfolio data
resources/
  css/app.css                App styles
  js/app.jsx                 Vite entry
  js/RootApp.jsx             React root component
  js/api/                    API client
  js/components/             Shared UI, tables, stock cards
  js/hooks/                  Shared React hooks
  js/i18n/                   Translations and language context
  js/layout/                 Authenticated app shell
  js/lib/                    Formatting helpers
  js/pages/                  Route-level pages
routes/
  api.php                    API routes
  web.php                    SPA fallback routes
tests/
  Feature/PortfolioApiTest.php
```

## Demo Account

After running the seeder:

```text
Email: demo@stockdivs.test
Password: password123
```

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
docker compose up -d postgres redis
php artisan migrate:fresh --seed
npm run build
```

For development, run the backend and frontend in separate terminals:

```bash
php artisan serve
npm run dev
```

Open the Laravel URL, usually `http://127.0.0.1:8000`.

## Docker Services

Local development uses PostgreSQL and Redis in Docker Compose:

```bash
docker compose up -d postgres redis
php artisan migrate:fresh --seed
```

Default connection settings:

```text
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=54320
DB_DATABASE=stockdivs
DB_USERNAME=stockdivs
DB_PASSWORD=stockdivs
```

Redis settings:

```text
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=63790
```

The persistent volumes are named `stockdivs-postgres-data` and `stockdivs-redis-data`, so data survives container restarts. PHPUnit still uses in-memory SQLite, array cache, and sync queue through `phpunit.xml` to keep tests fast and independent from Docker.

## API Overview

Auth:

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/user`
- `POST /api/forgot-password`

Stocks:

- `GET /api/stocks/search?query=AAPL`
- `GET /api/stocks/search?query=Сбер`
- `GET /api/stocks/{ticker}`
- `GET /api/stocks/{ticker}/quote`
- `GET /api/stocks/{ticker}/dividends`

Portfolio:

- `GET /api/portfolio`
- `GET /api/portfolio/summary`
- `GET /api/portfolio/transactions`
- `POST /api/portfolio/transactions`
- `PUT /api/portfolio/transactions/{id}`
- `DELETE /api/portfolio/transactions/{id}`
- `GET /api/portfolio/{ticker}`

Watchlist, dividends, alerts, and settings are exposed under `/api/watchlist`, `/api/dividends`, `/api/alerts`, and `/api/settings`.

## Market Data Provider

Local development uses `CompositeMarketDataProvider`:

- `MoexMarketDataProvider` reads Russian instruments from MOEX ISS without an API key.
- `FinnhubMarketDataProvider` reads global equities when `FINNHUB_API_KEY` is configured.
- `MockMarketDataProvider` is the fallback and keeps tests/offline development deterministic.

Supported provider modes:

```text
MARKET_DATA_PROVIDER=composite
MARKET_DATA_PROVIDER=moex
MARKET_DATA_PROVIDER=finnhub
MARKET_DATA_PROVIDER=mock
```

Relevant environment variables:

```text
MOEX_ISS_URL=https://iss.moex.com/iss
FINNHUB_API_URL=https://finnhub.io/api/v1
FINNHUB_API_KEY=
MARKET_DATA_QUOTE_TTL=10
```

Example refresh:

```bash
php artisan stocks:update-quotes --ticker=SBER
```

To add another source, create a provider class that implements `App\Services\MarketData\MarketDataProviderInterface`, expose credentials through `config/services.php`, and register it in `App\Providers\AppServiceProvider`.

## Scheduler

The command classes are available:

```bash
php artisan stocks:update-quotes
php artisan stocks:update-dividends
php artisan alerts:check
php artisan exchange-rates:update
```

The scheduler is configured in `routes/console.php`. Run it locally with:

```bash
php artisan schedule:work
```

When `QUEUE_CONNECTION=redis`, run a worker in another terminal for queued jobs:

```bash
php artisan queue:work
```

## Verification

```bash
php artisan test
npm run build
```

The feature suite includes a login-to-summary smoke test for the seeded demo portfolio.

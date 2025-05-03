# PSR test task

## Local Installation

Run:

```shell
cp .env.example .env
```

Change `EXCHANGE_RATES_API_KEY` if needed.

Run:

```shell
docker compose up -d --build

# flag `-v` is optional -- for verbosity
docker compose exec app php bin/console app:commission:calculate /app/var/downloads/transactions/input.txt -v

# run test
docker compose exec app composer test
```

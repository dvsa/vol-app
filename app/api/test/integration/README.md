# Integration test suite

Real repository queries and schema checks against the local development
database. Full documentation: [docs/app/testing.md](../../../../docs/app/testing.md)
(published at https://dvsa.github.io/vol-app/app/testing).

## Run

```bash
# prerequisites (once): docker compose up -d db && npm run refresh (repo root)
composer test:integration
```

Skips cleanly if the database is unreachable. Connection overrides:
`VOL_TEST_DB_HOST` / `VOL_TEST_DB_PORT` / `VOL_TEST_DB_USER` /
`VOL_TEST_DB_PASSWORD` / `VOL_TEST_DB_NAME`.

## Layout

- `src/Support/Database.php` — real EntityManager (reuses the phpstan-doctrine
  loader) + production repository wiring, no MVC bootstrap
- `src/IntegrationTestCase.php` — skip-if-no-db, per-test transaction rollback,
  `$this->repo('ShortName')` / `$this->em()`
- `src/Schema/SchemaDriftTest.php` — entity metadata vs Liquibase schema,
  against `schema-drift-baseline.txt` (regenerate:
  `REGENERATE_SCHEMA_DRIFT_BASELINE=1 vendor/bin/phpunit -c phpunit-integration.xml --filter SchemaDriftTest`)
- `src/Repository/` — real-DQL smoke tests (the VOL-7445 `toIterable()` export
  paths and friends)

The baseline holds triaged, genuine entity-vs-schema disagreements only —
cosmetic differences (comments, `DC2Type` hints, FK constraint names, views)
are normalised out of the comparison. New drift should be resolved with either
an entity fix (usually via the entity generator) or an olcs-etl migration, not
by growing the baseline.

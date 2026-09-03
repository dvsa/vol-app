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
- `src/Schema/SchemaComparison.php` — the deliberate reductions in fidelity the
  comparison applies, plus the actionable/inexpressible split; pinned by
  `SchemaComparisonTest`, which needs no database
- `src/Repository/` — real-DQL smoke tests (the VOL-7445 `toIterable()` export
  paths and friends)

The baseline is grouped under two headings carrying a count. Grouping is
presentational — every statement is compared, and headings are comments the
parser ignores:

- **actionable** — a real entity-vs-schema disagreement. Resolve with an entity
  fix (usually via the entity generator) or an olcs-etl migration, not by
  growing the baseline.
- **inexpressible from mapping** — differences on implicit ManyToMany join
  tables that no entity change can reach: `JoinTable` cannot name an index,
  declare a unique constraint, or map an extra column, and those tables have no
  entity to carry an `#[ORM\Index]`.

Only cosmetic differences that can never round-trip are normalised away — FK
constraint names, `yesno` storage types, views, and comments the mapping does
not declare. See `docs/app/testing.md` for why each earns its place, and why
the generated join-table index names are _not_ normalised.

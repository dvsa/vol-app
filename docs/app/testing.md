---
sidebar_position: 30
---

# Testing

The API has three layers of automated verification:

| Layer            | What runs                                                        | When                                    |
| ---------------- | ---------------------------------------------------------------- | --------------------------------------- |
| Unit + static    | PHPUnit (mocked), phpcs, psalm, phpstan (incl. phpstan-doctrine) | Every PR (`php.yaml`)                   |
| Integration      | Real repository queries and schema checks against a local MySQL  | Locally via `composer test:integration` |
| Functional (E2E) | WebDriver/Cucumber suites from `dvsa/vol-functional-tests`       | Post-deploy per environment (`cd.yaml`) |

This page documents the integration layer and the two baseline mechanisms that
guard the Doctrine entity metadata.

## Integration test suite

Location: `app/api/test/integration/` (namespace `Dvsa\OlcsTest\Integration`),
own PHPUnit config `app/api/phpunit-integration.xml`.

```bash
cd app/api
composer test:integration
```

### What it needs

A running local database with the schema and test data loaded:

```bash
docker compose up -d db
npm run refresh          # from the repo root; loads the Liquibase schema + testdata
```

If the database is unreachable the whole suite **skips** (it never fails a
machine without Docker). Connection defaults match the compose stack and can be
overridden with environment variables: `VOL_TEST_DB_HOST` (127.0.0.1),
`VOL_TEST_DB_PORT` (3306), `VOL_TEST_DB_USER` (root), `VOL_TEST_DB_PASSWORD`
(olcs), `VOL_TEST_DB_NAME` (olcs_be).

### How it works

`Support/Database.php` builds the minimal real object graph without booting the
MVC application: an EntityManager reusing the standalone phpstan-doctrine
loader (`phpstan-object-manager.php` — real entity metadata, custom DBAL types
and DQL functions), plus the query partial / db query / repository service
managers wired exactly as the production `RepositoryFactory` expects.
`IntegrationTestCase` gives each test a transaction that is rolled back in
`tearDown()`, so tests may insert whatever fixture data they need.

Repository tests fetch repositories by their production short name
(`$this->repo('LicenceVehicle')`) and run real DQL against the real schema —
the class of bug that mocked-query-builder unit tests cannot catch (see
VOL-7445, where `iterate()` → `toIterable()` broke three CSV exports that all
had green unit tests).

## The two metadata baselines

### ORM mapping validation (no database needed)

`test/module/Api/src/Entity/OrmMappingValidationTest.php` runs Doctrine's
`SchemaValidator::validateMapping()` over every entity. It lives in the normal
unit suite, so it runs on every PR.

Its baseline (`orm-mapping-validation-baseline.txt`) is **empty**: any mapping
error — a dangling `inversedBy`, a mismatched `mappedBy`, a broken association
target — fails the build immediately. Fix the metadata (usually via the
[entity generator](../entity-generator.md)) rather than adding to the baseline.

```bash
# after fixing errors, to confirm the baseline stays empty:
REGENERATE_ORM_MAPPING_BASELINE=1 vendor/bin/phpunit \
  test/module/Api/src/Entity/OrmMappingValidationTest.php
```

### Schema drift (integration suite)

`test/integration/src/Schema/SchemaDriftTest.php` compares the entity metadata
against the real, Liquibase-migrated schema — the sync-check half of
`orm:validate-schema`. The baseline (`test/integration/schema-drift-baseline.txt`)
records the known, triaged disagreements; the test fails only on **new** drift:
an entity change with no matching olcs-etl migration, or vice versa.

The comparison deliberately normalises what can never round-trip through ORM 2
metadata, so the baseline only contains real disagreements:

- **Comments** (column and table) are excluded — they are owned by olcs-etl
  DDL, and ORM 2's `JoinColumn` cannot carry options. Revisit on ORM 3.
- **Custom DBAL types** (`yesno`, `yesnonull`, `encrypted_string`) are
  compared by their storage type, so Doctrine's `DC2Type` comment hints —
  which the Liquibase DDL never carries — cannot diff.
- **Foreign key constraint names** are compared structurally (olcs-etl uses
  semantic names, Doctrine generates hashed ones).
- **View-backed entities** (`Entity\View`) and tables with no entity mapping
  (audit `*_hist` tables, ETL working tables, Liquibase bookkeeping) are
  excluded — neither can ever match by construction.

Everything left in the baseline is a genuine entity-vs-schema disagreement
(wrong lengths, precision, signedness, index differences) awaiting a decision:
fix the entity metadata, or fix the schema in olcs-etl. Shrinking it is welcome;
adding to it should be a conscious, reviewed act:

```bash
REGENERATE_SCHEMA_DRIFT_BASELINE=1 vendor/bin/phpunit \
  -c phpunit-integration.xml --filter SchemaDriftTest
```

## Adding integration tests

Extend `Dvsa\OlcsTest\Integration\IntegrationTestCase` and use `$this->repo()`
/ `$this->em()`. Good candidates are repository methods with non-trivial DQL
(joins, subqueries, streaming via `toIterable()`), anything that regressed in
production despite green unit tests, and query paths against database views.
Prefer selecting fixture rows from the seeded test data over hardcoding ids;
insert your own rows where the dataset is not enough — the per-test transaction
rolls them back.

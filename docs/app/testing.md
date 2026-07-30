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

This page documents the integration layer, the two baseline mechanisms that
guard the Doctrine entity metadata, and the two tests that guard output escaping
in the table render pipeline.

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

## Table output escaping

The table render pipeline does not escape. `ContentHelper::replaceContent()` is a
raw `str_replace` into `<td>{{content}}</td>`, so whether a column is safe
depends on whether something upstream escaped it. Two tests guard that, and they
guard **opposite** mistakes — you need both, because each is blind to what the
other catches.

| Test                         | Catches                                                | Visible to users?                     |
| ---------------------------- | ------------------------------------------------------ | ------------------------------------- |
| `TableEscapingInvariantTest` | a row value reaching the output unescaped              | No — it is an XSS hole                |
| `TableRenderSnapshotTest`    | something that was **not** a row value getting escaped | Yes — literal `&lt;b&gt;` on the page |

Both exist three times, once per table location, all sharing one harness in
olcs-common (`test/Common/src/Common/Service/Table/Harness/`): `app/internal`,
`app/selfserve` and olcs-common's own `Common/src/Common/Table/Tables`. Both run
on every PR — the apps via `php.yaml`, olcs-common via `php-lib.yaml`.

### The escaping contract

Escape by **provenance**, not by what the value looks like:

| Where the value came from        | What to do                                                                              |
| -------------------------------- | --------------------------------------------------------------------------------------- |
| Row data                         | Escape, always, whatever it looks like                                                  |
| Developer-authored markup        | Leave raw; escape the values you interpolate into it                                    |
| Another formatter's return value | Leave raw — escaping it double-escapes, and escaping its values is that formatter's job |

`TableBuilder::replaceContentEscapingValues()` exists for the middle case and is
greppable. The third case is the one that catches people out: wrapping
`$this->callFormatter(...)` in an escape call is always wrong.

### When the invariant test fails

A table you touched now emits a row value raw. Escape the value at the point it
is interpolated. The baseline (`table-escaping-baseline.txt` beside each test)
lists tables that are known exceptions and are exempt; it should only ever
shrink, and a listed table that stops leaking also fails, so the list cannot rot.
Tables the synthetic probe cannot drive are reported as **skipped**, which means
"not covered here", never "safe".

### When the snapshot test fails

Rendered output moved. If you meant it — a column added, a label reworded —
regenerate; if you did not, you have probably escaped developer markup, another
formatter's output, or a value that was already escaped where it was assigned.

```bash
# from the app or lib directory that failed
UPDATE_TABLE_SNAPSHOTS=1 vendor/bin/phpunit path/to/TableRenderSnapshotTest.php
```

Regenerate deliberately, and read the diff. Doing it reflexively to make a red
build green defeats the entire point of the test.

The snapshot stores a digest per table rather than the rendered HTML, because 185
tables of markup would be unreviewable. It renders against benign data
containing an ampersand — a value of plain alphanumerics would be unchanged by
escaping, so escaping it twice would be invisible. The per-render CSRF token is
normalised out; anything else non-deterministic in a render will make the digest
unstable and needs normalising in `TableEscapingHarness::normalise()`.

## Adding integration tests

Extend `Dvsa\OlcsTest\Integration\IntegrationTestCase` and use `$this->repo()`
/ `$this->em()`. Good candidates are repository methods with non-trivial DQL
(joins, subqueries, streaming via `toIterable()`), anything that regressed in
production despite green unit tests, and query paths against database views.
Prefer selecting fixture rows from the seeded test data over hardcoding ids;
insert your own rows where the dataset is not enough — the per-test transaction
rolls them back.

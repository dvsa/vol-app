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
guard the Doctrine entity metadata, and the three tests that guard output
escaping in the table render pipeline.

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
depends on whether something upstream escaped it. Three tests guard that, and no
one of them subsumes another — the first two catch **opposite** mistakes, and the
third reaches code the first two structurally cannot.

| Test                             | Catches                                                | Visible to users?                     |
| -------------------------------- | ------------------------------------------------------ | ------------------------------------- |
| `TableEscapingInvariantTest`     | a row value reaching the output unescaped              | No — it is an XSS hole                |
| `TableRenderSnapshotTest`        | something that was **not** a row value getting escaped | Yes — literal `&lt;b&gt;` on the page |
| `FormatterEscapingInvariantTest` | a formatter leaking, whatever any table does           | No — it is an XSS hole                |

The first two exist three times, once per table location, all sharing one harness
in olcs-common (`test/Common/src/Common/Service/Table/Harness/`): `app/internal`,
`app/selfserve` and olcs-common's own `Common/src/Common/Table/Tables`. The third
exists once, because formatters all live in olcs-common behind a single plugin
config. All run on every PR — the apps via `php.yaml`, olcs-common via
`php-lib.yaml`.

### Running them

Nothing here needs a database, a container or a network. All three are ordinary
PHPUnit tests and run as part of the normal suite; these are the commands for
running them alone.

```bash
# all three, for one location
cd lib/olcs-common   # or app/internal, or app/selfserve
vendor/bin/phpunit --filter 'Escaping|RenderSnapshot'

# one at a time
cd lib/olcs-common
vendor/bin/phpunit --filter TableEscapingInvariantTest
vendor/bin/phpunit --filter TableRenderSnapshotTest
vendor/bin/phpunit --filter FormatterEscapingInvariantTest   # olcs-common only
```

`FormatterEscapingInvariantTest` lives only in olcs-common. The other two need
running in **all three** locations, because each has its own table directory and
its own baseline — a change in olcs-common can move a digest in `app/selfserve`,
so a green lib is not evidence on its own.

Regenerating the snapshot is the one thing that is not just "run the test":

```bash
UPDATE_TABLE_SNAPSHOTS=1 vendor/bin/phpunit --filter TableRenderSnapshotTest
```

### Escaping is per output format

Escaping happens at the **source** here — a formatter escapes the row value it
interpolates — which is right for HTML and wrong for everything else. Six
controllers export a table as CSV (`ResponseHelperService::tableToCsv`), and a
CSV is not HTML: nothing downstream decodes it, so an operator called
"Smith & Sons Ltd" reaches the spreadsheet as `Smith &amp; Sons Ltd`.

`TableBuilder::renderBodyColumn()` therefore decodes entities when the content
type is CSV. That is the renderer's job, not the formatter's — the formatter has
no idea which format it is feeding. `TableRenderSnapshotTest` asserts absolutely
that no table emits an entity when rendered as CSV, so a new output format that
needs the same treatment fails the build rather than shipping mojibake.

Two things this does **not** fix, both pre-existing and worth their own tickets:
CSV fields are joined with `', '` and never quoted, so a value containing a comma
splits the row; and a cell beginning `=`, `+`, `-` or `@` is executed as a formula
by Excel and LibreOffice.

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

Adding a table to the baseline is not the fix, and neither is regenerating
anything — the baseline only shrinks.

**Every table definition in the repository renders.** There is no skip list: all
233 are driven against a hostile row, and a table that stops rendering fails the
build rather than quietly dropping out of the count. `FormatterEscapingInvariantTest`
still earns its place — `lva-psv-vehicles-readonly` renders with no data rows at
all, so its `StackValue` columns are exercised only there.

### When a type rejects the probe

`RecursiveProbe` answers any key to any depth with itself, which is what lets one
value drive every table without knowing their shapes. What it cannot do is
satisfy a **type**: `number_format()` wants a float, `new DateTime()` wants
something parseable. Rather than record those tables as undrivable, both
harnesses learn the constraint from the failure and retry — see
`RowProbeAdaptation`, which they share.

Two things make this honest rather than convenient:

- The substitution is learned, never declared. Nothing is replaced until the code
  actually rejects the probe, so the adaptation disappears by itself when the
  constraint does. A hand-maintained "these keys are numeric" map would rot the
  way a skip list does.
- Substitutions that **lose the payload** are recorded, and then proved. A string
  or an array of probes still carries the marker, so the assertion is unweakened;
  a number or a date cannot, so each of those goes through the isolation pass
  below, which ends with it either escaped or rejected by its own type. Both are
  proofs, so neither is recorded — see
  [Putting the payload back](#putting-the-payload-back).

Table-level adaptation substitutes at a **dotted path**, not a root key:
`publication.pubDate` can be a real date while
`publication.pubStatus.description` stays a probe. Replacing the whole
`publication` key instead would de-probe every sibling column to fix one, which
is the difference between "this value cannot carry a payload" and "this table is
no longer tested".

### Putting the payload back

A substituted value is not tested, and "not tested" is where a leak hides. Worse,
the substitution lands in the row **every column shares**: if one column parses
`createdOn` as a date and another interpolates it into markup, the date that
satisfies the first is what the second emits — so the substitution masks the leak
it should have found. The more constrained a value is, the better it is hidden.

So every payload-losing value is put back. One at a time, the marker is restored
at exactly that path while the rest of the row stays as the settled render left
it, and the render runs once more with **no adaptation** — recovering from the
constraint is what the pass exists to avoid.

**The outcome is binary: the value is safe, or it is not.** There is no third
category and no list of values that are "safe for a different reason" — that
distinction is real but it is not actionable, and a reader who has just written a
table cannot do anything with it.

Safe arrives two ways, and neither is weaker, so neither is recorded anywhere:

| Safe            | Meaning                                                                            |
| --------------- | ---------------------------------------------------------------------------------- |
| **escaped**     | The render survived and the payload came out escaped.                              |
| **constrained** | The value's own type rejected the payload before anything was written to the page. |

Calling the second one safe is a complete argument, not a lenient one, and it is
worth spelling out because it looks like a let-off. The type partitions every
possible value: one that **satisfies** it is a number or a strict date, neither of
which can contain `<`, `>`, `&` or a quote — and the output is derived from the
parsed value rather than copied from the input. One that **does not** is rejected
before a byte is written; in production that input is a 500, not a payload on a
page. There is no third case, so there is nothing left to test.

Not safe also arrives two ways, and both fail the build:

| Not safe     | Meaning                                                                                                                         |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------- |
| **leaking**  | The payload reached the output raw — a real leak nothing else could see, because the ordinary run substitutes this value first. |
| **unproven** | The render failed for a reason that is **not** its type rejecting it, so nothing was established either way.                    |

`unproven` is the one that keeps the argument above honest. "It threw, so it
cannot get out" only holds when the throw _is_ the type rejecting the payload —
an exception from anywhere else (a missing service, a formatter broken by an
unrelated change) would otherwise read as proof of safety, and go on reading that
way for as long as the breakage lasted. So the exception is checked against the
same question the adaptation loop asks to learn a constraint, and anything it does
not recognise is reported by name. There is no baseline section for it: it is
empty today, and an entry means someone has to look.

Reading the output buffer **even when the render throws** matters for the same
reason. A value echoed by one column and only later rejected by another is
already on the page; the throw does not retract it.

One path at a time, never in a batch. `AbstractConversationMessage::getFirstReadBy()`
returns early when `createdBy.id` equals the read's `user.id`, so restoring the
payload to both at once would skip the branch and report "no leak" about code
that never ran. Isolated, both render and come out escaped.

Attributing a failure to the right value is most of the work, and it is done in
four ways, narrowest first: the failing line, the statement below it (a
multi-line call puts its arguments there), a local alias of the row resolved back
to its path (`$licence = $data['licence']` … `$licence['goodsOrPsv']['id']`), and
the column config the formatter was handed, read out of the exception trace. Only
if all four come back empty does it widen — and only for strings, because `MARKER`
_is_ a string, so a widened substitution keeps its payload. Numeric and date
never widen; they would silently de-probe the row.

Two consequences worth knowing:

- The harness reads exception trace arguments, so `zend.exception_ignore_args` is
  pinned to `0` in all three `phpunit.xml.dist` files. With args stripped, the
  column config is invisible and those tables stop rendering.
- The row is harvested from the definition **and** from the formatter classes it
  names. A key only a formatter reads would otherwise arrive as `null`, which
  makes `isset()` false and skips the very branch that leaks.

One mechanism is worth knowing about, because it is not about probe data at all:
**shadowing**. Definitions resolve by bare name against a list of directories, so
two files sharing a basename cannot both be reached through one builder — the
first hit wins. Two pairs exist today in `app/internal`: `conditions.table.php`
(in `Bus/` and in `SubmissionSections/`) and `environmental-complaints.table.php`
(in the table root and in `SubmissionSections/`). The loser used to vanish from
the report entirely, which read as full coverage; it now gets a builder of its
own, with its directory ordered to win. Keys carry their directory prefix
(`SubmissionSections/conditions.table.php`) so the two stay distinguishable.

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

### The third test: formatters, directly

`FormatterEscapingInvariantTest` (olcs-common only — formatters all live there and
share one plugin config) calls every formatter directly with a hostile row.

It exists because driving formatters _through_ a table leaves two blind spots:

- a formatter reachable only from a table the probe cannot render never runs at all
- `TableEscapingHarness` builds its row by harvesting `['literal']` subscripts from
  the table **definition**, so a key read only inside a formatter class is absent
  from the row and arrives as `null` — the formatter runs, but its leaking branch
  does not

`Formatter\PrinterDocumentCategory` is the worked example: it interpolates
`$row['subCategory']['category']['description']` into an `<a>`, but
`admin-printers-exceptions.table.php` never names `subCategory`, so `isset()` is
false, the `'Default setting'` branch is taken, and the table-level test passes a
formatter that leaks in production.

This harness harvests keys from the formatter's own source (and its parents), so
that cannot happen here by construction. Neither test subsumes the other — the
table-level one still owns inline closures, cell attributes and wrapping.

All **149** formatters are driven, none leaks, and none is skipped. Keeping it
that way is the point of the baseline below.

### How the probe works

Worth understanding before you change a formatter's signature, because that is
what usually makes one of these tests go red for a reason that is not escaping.

`RecursiveProbe` is a row stand-in that answers **any** key, to **any** depth,
with itself, and stringifies to `<script>xss-probe</script>`. That is what lets
one object drive a hundred formatters without anyone writing a fixture per table:
whatever a formatter reaches for, it gets the marker, and the harness then asks
whether the marker reached the output unescaped.

A probe cannot satisfy a **type**, though. A formatter that calls
`number_format()` or parses a date rejects it outright. So the harness does not
declare types up front — it **learns them from the failure**:

1. call the formatter; if it returns, check the output for the marker
2. if it throws, read the engine's own wording to decide what the value has to be
   (`must be of type int|float` → numeric, `Failed to parse time string` → date…)
3. find which row key the failing expression touched, from the failing line first
   and the whole statement second
4. substitute a value of that type into that key, and go round again

Learning rather than declaring matters: a hand-maintained "these keys are
numeric" map would rot exactly the way a skip list does, whereas an adaptation
disappears by itself the moment the constraint does.

Most substitutions keep the payload — a string **is** the marker, and an array
holds probes. Numbers and dates cannot, so each of those is put back on its own
and proved rather than quietly counted as covered (see
[Putting the payload back](#putting-the-payload-back)).

Two formatters need more than this. `RecursiveProbe` cannot satisfy a type at
**depth** — `getSenderName(): string` returns `$row['createdBy'][…]['forename']`,
and substituting at the root only moves the failure to the next subscript. The
two conversation _message_ formatters therefore get a hand-written row from
`FormatterEscapingHarness::fixtures()`. A fixture is merged **over** the
generated row, so a key the formatter starts reading tomorrow still arrives as a
probe rather than missing.

### The two baseline sections

`formatter-escaping-baseline.txt`. Both are asserted, so neither can rot:

| Section     | Fails when                                                      |
| ----------- | --------------------------------------------------------------- |
| `[leaking]` | a formatter starts leaking, or a listed one stops (stale entry) |
| `[skipped]` | a formatter becomes undrivable, **or becomes drivable**         |

Asserting `[skipped]` matters more than it looks. A skip is not "safe" — it is
"unknown", and without this it would stay unknown long after the reason for it
disappeared. If a formatter is skipped because `number_format()` rejects the probe,
and someone later drops that call, the value starts flowing raw; comparing the set
means that shows up as a failure rather than sitting unnoticed behind a stale entry.

There is deliberately **no section for values a type constraint keeps off the
page**, and there used to be. Listing them said "safe, but for a different
reason", which is true and useless: nobody reading it for the first time can act
on it. Those values are safe outright — see
[Putting the payload back](#putting-the-payload-back) for why the argument is
complete rather than lenient — so they are proved on every run and not recorded.
A value that can be proved neither way fails the build by name instead.

### When a formatter's constructor or signature changes

The most common way to make this test red without touching escaping:

| Symptom in the failure                   | Usually means                                                                                  |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `unresolvable: …` in the skipped list    | the factory is broken, or a new dependency is missing from `HarnessContainer`                  |
| a formatter moved into `[skipped]`       | a new type constraint the probe cannot satisfy — add the service, or a fixture if it is nested |
| a formatter moved **out** of `[skipped]` | good news; delete the entry                                                                    |
| an `unproven` value in the failure       | the formatter throws before reaching the value, for a reason unrelated to the value's type     |

If you add a formatter dependency, add it to `HarnessContainer` as the **real**
class wherever the real thing does not need the world. A mock that answers
everything with `''` will let the formatter run while feeding it nothing, which
looks like coverage and is not: `StackHelperService` was mocked that way, and
`StackValue`, `NumberStackValue`, `UnlicensedVehicleWeight` and
`FeeTransactionDate` all counted as exercised while formatting an empty string.
Three live leaks were sitting behind it.

## Adding integration tests

Extend `Dvsa\OlcsTest\Integration\IntegrationTestCase` and use `$this->repo()`
/ `$this->em()`. Good candidates are repository methods with non-trivial DQL
(joins, subqueries, streaming via `toIterable()`), anything that regressed in
production despite green unit tests, and query paths against database views.
Prefer selecting fixture rows from the seeded test data over hardcoding ids;
insert your own rows where the dataset is not enough — the per-test transaction
rolls them back.

# Doctrine Entity Generator

## Overview

The Doctrine Entity Generator is a CLI tool that automatically generates Doctrine entity classes from your database schema. It creates a separation between generated code (abstract entities) and custom code (concrete entities), allowing you to regenerate entities without losing custom logic.

### Key Features

- Generates abstract entity classes with all database mappings
- Preserves custom code in concrete entity classes
- Supports VOL-specific field types (YesNo, YesNoNull, etc.)
- Handles complex relationships (OneToMany, ManyToOne, ManyToMany)
- Auto-detects and excludes join tables

### Generated File Structure

For each database table, the generator creates:

- `{Namespace}/Abstract{EntityName}.php` - Auto-generated abstract class with properties and mappings
- `{Namespace}/{EntityName}.php` - Concrete class for custom logic (only created if it doesn't exist)
- Test stub in `test/module/Api/src/Entity/{Namespace}/{EntityName}EntityTest.php`

The namespace is determined by `namespace.config.php` which maps entity names to subdirectories. For example:

- `Application` entity → `Application/AbstractApplication.php` and `Application/Application.php`
- `BusReg` entity → `Bus/AbstractBusReg.php` and `Bus/BusReg.php`
- `ContactDetails` entity → `ContactDetails/AbstractContactDetails.php` and `ContactDetails/ContactDetails.php`

## Configuration

The generator uses `app/api/data/db/EntityConfig.php` to customize entity generation. The configuration file handles special cases like:

- Custom class names for tables (e.g., `txn` → `Transaction`)
- Custom property names for columns
- VOL-specific field types
- Relationship mappings and cascade options

### VOL Field Types

The generator supports special VOL field types that are configured in EntityConfig.php:

- `yesno` - Maps Y/N database values to boolean
- `yesnonull` - Nullable version of yesno
- Custom encrypted types for sensitive data

## What the generator emits

The generated attributes are a faithful mirror of the introspected schema. These
rules are pinned by `test/module/Cli/src/Service/EntityGenerator/AttributeEmissionTest.php` —
if you change emission behaviour, update that test alongside it, and expect the
schema-drift baseline (see [Testing](app/testing.md)) to move.

### Columns

- **Unique keys** are emitted as `#[ORM\UniqueConstraint]` only. They are never
  also emitted as `#[ORM\Index]`: MySQL reports unique constraints as indexes,
  but emitting both with the same name makes DBAL's schema tooling throw
  (`IndexAlreadyExists`).
- **`unsigned` and `fixed` (CHAR) columns** carry matching entries in the Column
  attribute's `options` array. Foreign key columns don't need them: Doctrine
  derives a join column's type (including unsignedness) from the referenced
  identifier.
- **Column comments are not emitted.** They are owned by the database DDL in
  olcs-etl, and ORM 2's `JoinColumn` cannot carry options at all, so comments
  can never round-trip through entity metadata. The schema-drift comparison
  excludes them for the same reason. Revisit once on ORM 3, where `JoinColumn`
  supports `options`.

### Relationships

- **JoinColumn nullability is always explicit.** `JoinColumn`'s default is
  `nullable: true` — the opposite of `Column`'s — so a NOT NULL foreign key
  that omitted the argument would misreport the real constraint.
- **`onDelete` mirrors the database's referential action** where it differs
  from the default (`CASCADE` / `SET NULL`). This is DDL-level metadata only;
  application-level cascades are configured separately via EntityConfig
  `cascade` options.
- **`inversedBy` is emitted on an owning side if and only if EntityConfig
  declares the inverse side** for that column — the same condition under which
  the generator creates the inverse collection — and is resolved with the same
  pluralization rules (`PropertyNameResolver`, including its
  `PLURAL_OVERRIDES` map for irregulars like _appendix → appendices_), so the
  `inversedBy`/`mappedBy` pair always matches.
- **ManyToMany associations targeting RefData are unidirectional** (no
  `inversedBy`): RefData never declares inverse collections, so an
  `inversedBy` would point at a property that does not exist.

### Hand-written inverse sides: `generateInverse: false`

Some features (currently the Letter module) declare their inverse collections by
hand in the concrete entities, because they need `cascade`/`orphanRemoval`
options alongside custom logic. For those columns, EntityConfig sets
`'generateInverse' => false` inside the `inversedBy` config:

```php
'letter_instance_choice' => array(
    'letter_instance_id' => array(
        'inversedBy' => array(
            'entity' => 'LetterInstance',
            'property' => 'letterInstanceChoice',
            'generateInverse' => false
        )
    )
),
```

The owning side still emits `inversedBy` (pointing at the hand-written
collection), but the generator does not create a duplicate collection in the
abstract. Do **not** convert these to normal `inversedBy` config while the
hand-written properties exist — you would generate a clashing second
declaration.

A related trap: the `mappingConfig` key `skipManyToMany` suppresses the whole
owning-side _property_, not just its `inversedBy`. Making the stale
`Dvsa\Olcs\Api\Entity\RefData` entry match the real class name
(`...\Entity\System\RefData`) would delete real, in-use collections on the next
regeneration.

## Verifying a regeneration

A regeneration is only trustworthy if you can prove what changed and why. The
workflow:

1. **Commit (or stash) everything first**, so `git diff` afterwards shows only
   the regeneration.
2. Regenerate (see Usage below) and review `git diff --stat`. A regeneration
   with no generator or schema change should be a no-op; after a generator fix,
   every changed line should match the pattern the fix predicts.
3. Run the gates:

    ```bash
    vendor/bin/phpunit test/module/Api/src/Entity        # includes the ORM mapping gate
    vendor/bin/phpunit test/module/Cli/src/Service/EntityGenerator
    composer test:integration                            # schema drift vs baseline
    composer run-script phpstan
    ```

The ORM mapping validation test has an **empty baseline** — any mapping error a
regeneration introduces fails immediately. The schema-drift baseline holds the
known, triaged entity-vs-schema disagreements; new statements mean the entities
and the Liquibase schema disagree in a new way. See
[Testing](app/testing.md) for how both baselines work and when to regenerate
them.

If the generator fails with `Module (Dvsa\Olcs\Transfer) could not be
initialized`, the cli container has probably lost its in-tree lib mounts after
a host-side `composer install` — see
[Local Setup troubleshooting](app/local-setup/index.md#troubleshooting).

## Usage

### Basic Usage (Recommended)

The command introspects the live database schema, so it must run somewhere with
database access — with the local Docker stack up, run it inside the `cli` container.
The standard workflow is to regenerate entities in place:

```bash
docker compose exec -w /var/www/html cli \
  vendor/bin/laminas --container=config/container-cli.php entity:generate --replace
```

(From within the container, or any environment with a `db` host, the command is
`vendor/bin/laminas --container=config/container-cli.php entity:generate --replace`
run from `app/api`.)

This will:

1. Regenerate all abstract entities from the current database schema
2. Leave concrete entity classes untouched (preserving custom logic)
3. Create concrete entities only if they don't exist

**Note:** The `--replace` flag only replaces abstract entities, not concrete ones, so your custom code is safe.

### Command Options

| Option              | Description                                           | Default                    |
| ------------------- | ----------------------------------------------------- | -------------------------- |
| `-r, --replace`     | Replace existing abstract entities (recommended)      | `false`                    |
| `-d, --dry-run`     | Preview what would be generated without writing files | `false`                    |
| `-o, --output-path` | Output directory for generated entities               | `/tmp/generated-entities`  |
| `-c, --config`      | Path to EntityConfig.php file                         | `data/db/EntityConfig.php` |

### Common Scenarios

The examples below use `entity:generate` as shorthand for the full invocation shown
in Basic Usage.

#### 1. Standard Regeneration

After database schema changes:

```bash
entity:generate --replace
```

#### 2. Preview Changes

See what would be generated without making changes:

```bash
entity:generate --dry-run
```

#### 3. Generate for Review

If you want to review changes before applying:

```bash
entity:generate -o /tmp/review-entities
# Then compare with existing:
diff -r /tmp/review-entities app/api/module/Api/src/Entity
```

### Output Example

```
OLCS Entity Generator
=====================

Generated 125 entities in 2.34 seconds
```

## Important Notes

- **Always commit your code before regenerating** - while concrete entities are safe, it's good practice
- **Never hand-edit abstract entities** - anything written there (including docblock
  documentation) is silently lost on the next regeneration. Document entity behaviour
  in the concrete class; column descriptions belong in the database column comments
  (managed in olcs-etl), which the generator copies into the generated docblocks
- **Regeneration output reflects your local database schema** - make sure your local
  database is up to date with the latest olcs-etl changesets before regenerating,
  otherwise the diff will mix schema drift with your intended changes
- The generator automatically detects join tables for ManyToMany relationships and excludes them
  (tables marked with `_owner` in EntityConfig.php); it will not generate or refresh an
  entity class for such tables
- Tables with `@settings['ignore']` in their database comment will be skipped
- The generator preserves the inheritance structure: abstract entities contain generated code, concrete entities contain custom logic
- After regenerating, run your test suite to ensure everything still works correctly
  (`vendor/bin/phpunit test/module/Api/src/Entity` plus `composer run-script phpstan`)

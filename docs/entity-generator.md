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

# Database Migrations with Doctrine

We are migrating from Liquibase to Doctrine Migrations to manage database schema changes. This tool provides version control for the database schema and makes it easy to coordinate database changes across different environments.

## Migration from Liquibase

Previously, we used Liquibase for database migrations, which maintained its changes in the `DATABASECHANGELOG` table. Doctrine Migrations tracks migrations in a new `migrations` table. Both systems can coexist during the transition period.

## Configuration

Doctrine Migrations configuration is managed through two files in the project root:

-   `migrations.php` - Configures migration settings
-   `migrations-db.php` - Configures database connection details

These files automatically load the appropriate environment-specific configurations following our standard config loading process (global, local, environment-specific overrides etc).

## Directory Structure

Migrations are stored in:

```
data/Migrations/
```

Each migration file follows the naming convention: `VersionYYYYMMDDHHMMSS.php`

## Available Commands

Run migrations commands using:

```bash
./vendor/bin/laminas --container=config/container-cli.php migrations:[command]
```

Available commands:

| Command               | Description                                         |
| --------------------- | --------------------------------------------------- |
| `migrations:current`  | Shows current migration version                     |
| `migrations:execute`  | Manually execute specific migrations up/down        |
| `migrations:generate` | Generate a blank migration file                     |
| `migrations:latest`   | Show the latest available migration version         |
| `migrations:list`     | List all migrations and their status                |
| `migrations:migrate`  | Run migrations up to specified version or latest    |
| `migrations:status`   | Show overall migration status                       |
| `migrations:version`  | Manually manage migration versions in version table |
| `migrations:diff`     | Does not work correctly yet. To be fixed!           |

## Initial Setup

### Local Development

#### First Time Setup

1. Initialize the migrations table:

```bash
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate first
```

#### Running unactioned migrations

2. Run all existing migrations:

```bash
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate
```

### Day-to-Day Development

When working with migrations during development:

1. Pull latest changes and run any new migrations:

```bash
git pull origin main
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate
```

2. Creating a new migration:

Create a new branch, then:

```bash
# Generate a timestamped migration file
./vendor/bin/laminas --container=config/container-cli.php migrations:generate
```

3. Edit the generated file in `data/Migrations/` to add your schema changes

4. Test your migration:

```bash
# Run the migration
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate

# Check migration status
./vendor/bin/laminas --container=config/container-cli.php migrations:status
```

5. Test rolling back if needed:

```bash
# Roll back the last migration
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate prev

# Roll back to a specific version
./vendor/bin/laminas --container=config/container-cli.php migrations:migrate 'Migrations\Version20241030095525'
```

### Non-Production/Production Environments

TBD - To be completed with input from app dev and platform team on best way to do first time run etc.

:::tip

## Best Practices

-   Include both the up() and down() methods in your migrations
-   Test migrations both up and down locally before committing
-   Keep migrations atomic - one conceptual change per migration
-   Include meaningful descriptions in migration classes
-   Be aware of the differences between local and nonprod/prod datasets that might affect your migrations.
-   Use transactions where possible (controlled by `all_or_nothing` config)
-   Document complex migrations with clear comments
    :::

## Troubleshooting

If you encounter issues:

1. Check migration status:

```bash
./vendor/bin/laminas --container=config/container-cli.php migrations:status
```

2. Clear cache if needed:

```bash
rm -rf data/cache/*
rm -rf data/DoctrineORMModule/Proxy/*
composer dump-autoload
```

4. Check logs for detailed error messages

## Migration Table

Doctrine Migrations maintains its state in the `migrations` table with the following structure:

-   `version` - Migration version number (YYYYMMDDHHMMSS)
-   `executed_at` - When the migration was run
-   `execution_time` - How long the migration took to run

You can view the migration history with:

```bash
./vendor/bin/laminas --container=config/container-cli.php migrations:list
```

## Example Migration

```php
<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20240131123456 extends AbstractMigration
{
   public function up(Schema $schema): void
   {
       // Add new column after lic_no
       $this->addSql('ALTER TABLE licence ADD COLUMN new_column VARCHAR(50) AFTER lic_no');

       // Add unique index
       $this->addSql('CREATE UNIQUE INDEX idx_licence_new_column ON licence (new_column)');
   }

   public function down(Schema $schema): void
   {
       // Remove index first
       $this->addSql('DROP INDEX idx_licence_new_column ON licence');

       // Remove column
       $this->addSql('ALTER TABLE licence DROP COLUMN new_column');
   }
}
```

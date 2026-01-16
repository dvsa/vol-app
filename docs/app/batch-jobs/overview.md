# Batch Commands Overview

:::info
This document provides an overview of the Batch Commands available in VOL.

The Batch Jobs are Laminas CLI Commands. Most are just wrappers that call Command Handlers which implement the business logic.
:::

:::tip
To run these commands locally, run in the following format from the api root directory:

```bash
./vendor/bin/laminas --container=config/container-cli.php -v [command] [options]
```

If you are running the app using containers, prefix the command with `docker exec vol-app-cli-1`:

```bash
docker exec vol-app-cli-1 ./vendor/bin/laminas --container=config/container-cli.php -v [command] [options]
```

:::

## CLI Command Structure and Registration

:::info
CLI Commands are defined in the following directory:

```
app/api/module/Cli/src/Command
```

:::

### Command Structure

Most CLI Commands in our system extend `Dvsa\Olcs\Cli\Command\AbstractCommand`. This base class provides shared functionality for logging and dry run behaviors, promoting consistency across our command implementations.

### Command Registration

Currently, all CLI Commands are registered in the service container using a `ConfigAbstractFactory` block. This configuration can be found in:

```
app/api/module/Cli/config/module.config.php
```

This approach allows for automated dependency injection and centralized configuration of our CLI Commands.

:::tip
When creating a new CLI Command, remember to:

1. Place it in the correct directory
2. Extend `AbstractCommand` if appropriate
3. Register it in the `module.config.php` file
   :::

## Available Commands

:::info
To list all registered commands, run the following:

```bash
./vendor/bin/laminas --container=config/container-cli.php list
```

To see more details for a specific command, and any parameters it accepts (if any) you can add --help after the command name. e.g:

```bash
./vendor/bin/laminas --container=config/container-cli.php -v batch:data-gov-uk-export --help
```

:::

## DataGovUK & DVA-NI Export Commands - Upload to S3

The `batch:data-dva-ni-export` and `batch:data-gov-uk-export` commands are used to export data to CSV files for use in other departments. These commands also upload the generated CSV files to an S3 bucket.

### Data DVA NI EXPORT

`batch:data-dva-ni-export` supports one `report-name` parameter of `ni-operator-licence` - A tarball with a csv file and a manifest hash file is created and uploaded to a S3 bucket.

### Data GOV UK Export

`batch:data-gov-uk-export` supports the following `report-name` parameters:

- `operator-licence`
- `bus-registered-only`
- `bus-variation`
- `psv-operator-list`
- `international-goods`

Each will create 1 or more CSV files, and upload these directly to an S3 bucket.

`psv-operator-list` and `international-goods` each also send an email to an address specified in a System Parameter

## Command Examples with Parameters

Here are some examples of commands that require parameters to operate.

:::note
These examples assume you are in the `app/api` directory.
:::

```bash
./vendor/bin/laminas --container=config/container-cli.php batch:ch-vs-olcs-diffs -v --path=/tmp/
./vendor/bin/laminas --container=config/container-cli.php batch:data-dva-ni-export -v --report-name=ni-operator-licence
./vendor/bin/laminas --container=config/container-cli.php batch:data-gov-uk-export -v --report-name=operator-licence
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --populate
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --precheck
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --delete
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --postcheck
```

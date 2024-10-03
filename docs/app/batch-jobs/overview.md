# Batch Commands Overview

:::info
This document provides an overview of the Batch Commands available in VOL. The top level batch jobs which are called are implemented as Laminas CLI commands, but generally are wrappers for CQRS Commands. The actual business logic is implemented in the associated Command Handlers.
:::

:::tip
To run these commands locally, use the following format from the api root directory:

```bash
./vendor/bin/laminas --container=config/container-cli.php -v [command] [options]
```

:::

## Available Commands

Here's a list of available batch commands:

| Command                                    | Description                                                                                                                                           |
| ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| `batch:ch-vs-olcs-diffs`                   | Find differences between Companies House and OLCS data and export them.                                                                               |
| `batch:clean-up-variations`                | Clean up abandoned variations.                                                                                                                        |
| `batch:cns`                                | Process licences for Continuation Not Sought (CNS).                                                                                                   |
| `batch:create-psv-licence-surrender-tasks` | Create tasks to surrender PSV licences that have expired.                                                                                             |
| `batch:data-dva-ni-export`                 | Export to csv for Northern Ireland                                                                                                                    |
| `batch:data-gov-uk-export`                 | Export to csv for data.gov.uk                                                                                                                         |
| `batch:data-retention`                     | Run data retention rules                                                                                                                              |
| `batch:database-maintenance`               | Perform database management tasks, e.g., changing is_irfo flags                                                                                       |
| `batch:digital-continuation-reminders`     | Generate digital continuation reminders.                                                                                                              |
| `batch:duplicate-vehicle-removal`          | Duplicate vehicle removal                                                                                                                             |
| `batch:duplicate-vehicle-warning`          | Send duplicate vehicle warning letters                                                                                                                |
| `batch:enqueue-ch-compare`                 | Enqueue Companies House lookup for all Organisations                                                                                                  |
| `batch:expire-bus-registration`            | Expire bus registrations past their end date.                                                                                                         |
| `batch:flag-urgent-tasks`                  | Flag tasks as urgent                                                                                                                                  |
| `batch:import-users-from-csv`              | Import user from csv file                                                                                                                             |
| `batch:inspection-request-email`           | Process inspection request email                                                                                                                      |
| `batch:interim-end-date-enforcement`       | Enforces interim end date by checking applications under consideration with an in-force interim that have an end date of the previous day or earlier. |

## DataGovUK & DVA-NI Export Commands - Upload to S3

The `batch:data-dva-ni-export` and `batch:data-gov-uk-export` commands are used to export data to CSV files for use in other departments. These commands also upload the generated CSV files to an S3 bucket.

### Data DVA NI EXPORT

`batch:data-dva-ni-export` supports one `report-name` parameter of `ni-operator-licence` - A tarball with a csv file and a manifest hash file is created and uploaded to a S3 bucket.

### Data GOV UK Export

`batch:data-gov-uk-export` supports the following `report-name` parameters:

-   `operator-licence`
-   `bus-registered-only`
-   `bus-variation`
-   `psv-operator-list`
-   `international-goods`

Each will create 1 or more CSV files, and upload these directly to an S3 bucket.

`psv-operator-list` and `international-goods` each also send an email to an address specified in a System Parameter

## Queue Commands

In addition to batch commands, we also have several queue-related commands:

| Command                         | Description                                                     |
| ------------------------------- | --------------------------------------------------------------- |
| `queue:company-profile-dlq`     | Processes the Company Profile DLQ (Dead Letter Queue) items.    |
| `queue:process-company-profile` | Processes the Company Profile queue items.                      |
| `queue:process-insolvency`      | Processes the Process Insolvency queue items.                   |
| `queue:process-insolvency-dlq`  | Processes the Process Insolvency DLQ (Dead Letter Queue) items. |
| `queue:process-queue`           | Processes queue items.                                          |
| `queue:transxchange-consumer`   | Processes TransXChange queue items.                             |

## Command Examples with Parameters

Here are some examples of commands that require parameters to operate.

:::note
These examples assume you are in the `app/api` directory.
:::

```bash
./vendor/bin/laminas --container=config/container-cli.php batch:ch-vs-olcs-diffs -v --path=/tmp/
./vendor/bin/laminas --container=config/container-cli.php batch:clean-up-variations -v
./vendor/bin/laminas --container=config/container-cli.php batch:cns -v
./vendor/bin/laminas --container=config/container-cli.php batch:create-psv-licence-surrender-tasks -v
./vendor/bin/laminas --container=config/container-cli.php batch:data-dva-ni-export -v --report-name=ni-operator-licence
./vendor/bin/laminas --container=config/container-cli.php batch:data-gov-uk-export -v --report-name=operator-licence
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --populate
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --precheck
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --delete
./vendor/bin/laminas --container=config/container-cli.php batch:data-retention -v --postcheck
```

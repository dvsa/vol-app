# VOL Job Scheduler Comprehensive Report

## Summary

This document serves as a summary for the VOL Job Scheduler config from `vol-jobscheduler` repository which powers the production VOL app at the moment while it is running in its EC2 incarnation. It is meant to be useful as reference
as we migrate away from EC2 to containerised environments.

The VOL PROD Job Scheduler manages **74 unique job types** in the Integration/Production environment, organized into 10 functional categories. The system processes critical business operations including licensing, permits, vehicle management, payments, and government reporting through a combination of continuous queue processing and scheduled batch jobs.

## Job Categories & Types

### 1. Queue Processing Jobs (11 jobs)

**Purpose**: Core queue management with specialized processors for different queue types

#### General Queue Processing

- **Job Name**: `int.olcs.api.process_queue.general`
- **Schedule**: Every 2 minutes, Mon-Fri, 00:00-23:59
- **Command**: `queue:process-queue`
- **Excluded Queue Types**:
    - `que_typ_ch_compare`
    - `que_typ_create_gds_vehicle_list`
    - `que_typ_create_psv_vehicle_list`
    - `que_typ_disc_printing`
    - `que_typ_print`
    - `que_typ_disc_printing_print`
    - `que_typ_create_com_lic`
    - `que_typ_remove_deleted_docs`
    - `que_typ_permit_generate`
    - `que_typ_permit_print`
    - `que_typ_run_ecmt_scoring`
    - `que_typ_accept_ecmt_scoring`
    - `que_typ_irhp_permits_allocate`

#### Specialized Queue Processors

| Job Name                                        | Queue Type                                                                                    | Schedule                  | Purpose            |
| ----------------------------------------------- | --------------------------------------------------------------------------------------------- | ------------------------- | ------------------ |
| `int.olcs.api.process_queue.community_licences` | `que_typ_create_com_lic`                                                                      | Every 2 minutes, Daily    | Community licenses |
| `int.olcs.api.process_queue.disc_generation`    | `que_typ_create_gds_vehicle_list`, `que_typ_create_psv_vehicle_list`, `que_typ_disc_printing` | Every 2 minutes, Mon-Fri  | Disc generation    |
| `int.olcs.api.process_queue.disc_print`         | `que_typ_disc_printing_print`                                                                 | Every 15 minutes, Mon-Fri | Disc printing      |
| `int.olcs.api.process_queue.print`              | `que_typ_print`                                                                               | Every 2 minutes, Mon-Fri  | General printing   |
| `int.olcs.api.process_queue.permit_generation`  | `que_typ_permit_generate`                                                                     | Every 2 minutes, Daily    | Permit generation  |
| `int.olcs.api.process_queue.permit_print`       | `que_typ_permit_print`                                                                        | Every 15 minutes, Daily   | Permit printing    |
| `int.olcs.api.process_queue.ecmt_scoring`       | `que_typ_run_ecmt_scoring`                                                                    | Every 2 minutes, Daily    | ECMT scoring       |
| `int.olcs.api.process_queue.ecmt_accept`        | `que_typ_accept_ecmt_scoring`                                                                 | Every 2 minutes, Daily    | ECMT acceptance    |
| `int.olcs.api.process_queue.irhp_allocate`      | `que_typ_irhp_permits_allocate`                                                               | Every 2 minutes, Daily    | IRHP allocation    |

### 2. Queue Consumer Jobs (5 jobs)

**Purpose**: External system integration via message queues

| Job Name                                     | Command                         | Schedule                | Purpose                 |
| -------------------------------------------- | ------------------------------- | ----------------------- | ----------------------- |
| `int.olcs.api.queue_consumer.chget`          | `queue:process-company-profile` | Every 5 minutes, Daily  | Companies House GET     |
| `int.olcs.api.queue_consumer.chget-dlq`      | `queue:company-profile-dlq`     | Every 30 minutes, Daily | Companies House DLQ     |
| `int.olcs.api.queue_consumer.insolvency`     | `queue:process-insolvency`      | Every 30 minutes, Daily | Insolvency processing   |
| `int.olcs.api.queue_consumer.insolvency-dlq` | `queue:process-insolvency-dlq`  | Every 30 minutes, Daily | Insolvency DLQ          |
| `int.olcs.api.queue_consumer.transxchange`   | `queue:transxchange-consumer`   | Every 2 minutes, Daily  | Transport data exchange |

### 3. Batch Processing Jobs (15 jobs)

**Purpose**: Core business logic execution

#### License Management

| Job Name                                | Command                         | Schedule                     | Purpose                                      |
| --------------------------------------- | ------------------------------- | ---------------------------- | -------------------------------------------- |
| `int.olcs.api.auto_cns`                 | `batch:cns`                     | Monthly, 7th, 18:30          | Process Licences for Continuation Not Sought |
| `int.olcs.api.change_licence_status`    | `batch:licence-status-rules`    | Hourly, Mon-Fri, 09:00-18:30 | License revocations/suspensions/curtailments |
| `int.olcs.api.expire_bus_registrations` | `batch:expire-bus-registration` | Daily, Mon-Fri, 18:00        | Expire bus registrations                     |

#### Vehicle Management

| Job Name                                 | Command                           | Schedule              | Purpose                         |
| ---------------------------------------- | --------------------------------- | --------------------- | ------------------------------- |
| `int.olcs.api.duplicate_vehicle_removal` | `batch:duplicate-vehicle-removal` | Daily, Mon-Fri, 18:30 | Remove duplicate vehicles       |
| `int.olcs.api.duplicate_vehicle_warning` | `batch:duplicate-vehicle-warning` | Daily, Mon-Fri, 18:15 | Send duplicate vehicle warnings |

#### Permit Management

| Job Name                                    | Command                         | Schedule              | Purpose                      |
| ------------------------------------------- | ------------------------------- | --------------------- | ---------------------------- |
| `int.olcs.api.mark_expired_permits`         | `permits:mark-expired-permits`  | Daily, Mon-Fri, 18:15 | Mark expired permits         |
| `int.olcs.api.permit_close_expired_windows` | `permits:close-expired-windows` | Daily, Mon-Fri, 00:45 | Close expired permit windows |
| `int.olcs.api.withdraw_unpaid`              | `permits:withdraw-unpaid`       | Daily, Mon-Fri, 00:45 | Withdraw unpaid applications |

#### Payment & Document Processing

| Job Name                               | Command                  | Schedule                 | Purpose                           |
| -------------------------------------- | ------------------------ | ------------------------ | --------------------------------- |
| `int.olcs.api.resolve_payments`        | `batch:resolve-payments` | Every 5 minutes, Mon-Fri | Resolve pending CPMS payments     |
| `int.olcs.api.process_inbox_documents` | `batch:process-inbox`    | Daily, Mon-Fri, 18:45    | Process inbox documents           |
| `int.olcs.api.process_ntu`             | `batch:process-ntu`      | Daily, Mon-Fri, 18:00    | Process not taken up applications |

#### External Integrations

| Job Name                              | Command                          | Schedule                     | Purpose                        |
| ------------------------------------- | -------------------------------- | ---------------------------- | ------------------------------ |
| `int.olcs.api.companies_house_alerts` | `batch:enqueue-ch-compare`       | 2nd & 4th Wednesday, 18:00   | Process Companies House alerts |
| `int.olcs.api.ecms_import`            | `batch:inspection-request-email` | Hourly, Mon-Fri, 09:00-18:30 | Import ECMS emails             |

### 4. Data Export Jobs (6 jobs)

**Purpose**: Government and regulatory reporting

| Job Name                                  | Command                                                      | Schedule            | Purpose                              |
| ----------------------------------------- | ------------------------------------------------------------ | ------------------- | ------------------------------------ |
| `int.olcs.api.tclic`                      | `batch:data-gov-uk-export --report-name=operator-licence`    | Friday, 18:00       | Transport Commissioner licenses      |
| `int.olcs.api.tcreg`                      | `batch:data-gov-uk-export --report-name=bus-registered-only` | Friday, 18:00       | Transport Commissioner registrations |
| `int.olcs.api.tcvar`                      | `batch:data-gov-uk-export --report-name=bus-variation`       | Friday, 18:00       | Transport Commissioner variations    |
| `int.olcs.api.dvaoplic`                   | `batch:data-dva-ni-export --report-name=ni-operator-licence` | Wednesday, 10:00    | DVA NI operator licenses             |
| `int.olcs.api.export_international_goods` | `batch:data-gov-uk-export --report-name=international-goods` | Monthly, 5th, 18:30 | International goods licenses         |
| `int.olcs.api.export_psv_operator_list`   | `batch:data-gov-uk-export --report-name=psv-operator-list`   | Monthly, 5th, 18:30 | PSV operator list for DfT            |

### 5. Data Management Jobs (8 jobs)

**Purpose**: Data retention and database operations

#### Data Retention Pipeline

| Job Name                                  | Command                              | Schedule       | Purpose                         |
| ----------------------------------------- | ------------------------------------ | -------------- | ------------------------------- |
| `int.olcs.api.data_retention_precheck`    | `batch:data-retention --precheck`    | Manual trigger | Data retention pre-check        |
| `int.olcs.api.data_retention_populate`    | `batch:data-retention --populate`    | Manual trigger | Populate retention data         |
| `int.olcs.api.data_retention_delete`      | `batch:data-retention --delete`      | Manual trigger | Delete retained data            |
| `int.olcs.api.data_retention_delete_docs` | Queue: `que_typ_remove_deleted_docs` | Manual trigger | Delete documents from filestore |
| `int.olcs.api.data_retention_postcheck`   | `batch:data-retention --postcheck`   | Manual trigger | Post-deletion verification      |

#### Database Operations

| Job Name                        | Command                                 | Schedule       | Purpose                     |
| ------------------------------- | --------------------------------------- | -------------- | --------------------------- |
| `int.olcs.dbam.generate_anondb` | `/mnt/data/anondump/populate_anondb.sh` | Monday, 09:00  | Generate anonymous database |
| `int.olcs.dbam.import_anondb`   | `/mnt/data/anondump/import_anondb.sh`   | Monday, 09:00  | Import anonymous database   |
| `int.olcs.dbam.sas_mi_extract`  | `/mnt/data/olcsdump/sas-mi-extract.sh`  | Mon-Fri, 10:00 | SAS MI data extract         |

### 6. Maintenance & Utility Jobs (11 jobs)

**Purpose**: System maintenance and task management

#### Task Management

| Job Name                                          | Command                                    | Schedule                     | Purpose                        |
| ------------------------------------------------- | ------------------------------------------ | ---------------------------- | ------------------------------ |
| `int.olcs.api.create_psv_licence_surrender_tasks` | `batch:create-psv-licence-surrender-tasks` | Monthly, 7th, 18:30          | Generate PSV surrender tasks   |
| `int.olcs.api.flag_urgent_tasks`                  | `batch:flag-urgent-tasks`                  | Hourly, Mon-Fri, 09:00-18:30 | Flag urgent tasks              |
| `int.olcs.api.batch_clean_variations`             | `batch:clean-up-variations`                | Daily, Mon-Fri, 09:00        | Clean up abandoned variations  |
| `int.olcs.api.digital_continuation_reminders`     | `batch:digital-continuation-reminders`     | Daily, Mon-Fri, 18:30        | Digital continuation reminders |
| `int.olcs.api.lasttmlic`                          | `batch:last-tm-letter`                     | Mon-Sat, 08:00               | Last transport manager letters |
| `int.olcs.api.interim_end_date_enforcement`       | `batch:interim-end-date-enforcement`       | Daily, 02:00                 | Interim end date enforcement   |

#### System Maintenance

| Job Name                             | Command                              | Schedule                     | Purpose                            |
| ------------------------------------ | ------------------------------------ | ---------------------------- | ---------------------------------- |
| `int.olcs.api.remove_read_audit`     | `batch:remove-read-audit`            | Friday, 18:00                | Remove old read audit records      |
| `int.olcs.api.tmp_cleanup`           | Shell script for `/tmp` cleanup      | Daily, Mon-Fri, 09:00        | Clean temporary files              |
| `int.olcs.update_crl`                | `/usr/local/bin/apply_crl_update.sh` | Daily, Mon-Fri, 09:00        | Update certificate revocation list |
| `int.olcs.iuweb.webtier_clam_update` | `sudo /usr/bin/freshclam update`     | Hourly, Mon-Fri, 09:00-18:00 | Update ClamAV on IU web tier       |
| `int.olcs.ssweb.webtier_clam_update` | `sudo /usr/bin/freshclam update`     | Hourly, Mon-Fri, 09:00-18:00 | Update ClamAV on SS web tier       |

### 7. Document Store Jobs (7 jobs)

**Purpose**: File and document management via WebDAV

| Job Name                                               | Command                             | Schedule                | Purpose                                       |
| ------------------------------------------------------ | ----------------------------------- | ----------------------- | --------------------------------------------- |
| `int.olcs.webdav.docstore`                             | `/usr/local/bin/docstore_import.sh` | Daily, Mon-Fri, 09:30   | Process document store import                 |
| `int.olcs.webdav.docstore.export_guides`               | Export guides                       | Tuesday, 08:00          | Export document guides                        |
| `int.olcs.webdav.docstore.export_templates`            | Export templates                    | Wednesday, 08:00        | Export document templates                     |
| `int.olcs.webdav.docstore.move_orphan_files.documents` | Move orphan files                   | Mon-Tue, Thu-Fri, 08:00 | Stage 1: Move orphaned document files         |
| `int.olcs.webdav.docstore.move_orphan_files.olbs`      | Move orphan files                   | Mon, Wed-Fri, 08:00     | Stage 1: Move orphaned OLBS files             |
| `int.olcs.webdav.docstore.purge_orphan_dirs.documents` | Purge orphan directories            | Wed-Thu, 18:00          | Stage 2: Delete orphaned document directories |
| `int.olcs.webdav.docstore.purge_orphan_dirs.olbs`      | Purge orphan directories            | Wed-Thu, 18:00          | Stage 2: Delete orphaned OLBS directories     |

### 8. Monitoring & Reporting Jobs (4 jobs)

**Purpose**: System health and queue monitoring

| Job Name                            | Command                             | Schedule                  | Purpose                    |
| ----------------------------------- | ----------------------------------- | ------------------------- | -------------------------- |
| `int.olcs.api.process_queue.size`   | SQL query to count queue items      | Every 15 minutes, Mon-Fri | Monitor queue sizes        |
| `int.olcs.api.process_queue.failed` | SQL query for failed items          | Every 15 minutes, Mon-Fri | Report failed queue items  |
| `int.olcs.api.vi_extract`           | `/mnt/data/viextract/vi-extract.sh` | Daily, Mon-Fri, 18:00     | Vehicle inspection extract |
| `int.olcs.api.olcs_db_backup`       | `/usr/local/bin/backupapirds.sh`    | Daily, Mon-Fri, 09:00     | Database backup            |

### 9. Job Control & Deployment Jobs (6 jobs)

**Purpose**: System deployment and job management

| Job Name                              | Schedule | Purpose                              |
| ------------------------------------- | -------- | ------------------------------------ |
| `int.disable_all_jobs`                | Manual   | Disable all jobs for maintenance     |
| `int.enable_all_jobs`                 | Manual   | Re-enable all jobs after maintenance |
| `int.disable_jobs_pre_deployment`     | Manual   | Disable jobs before deployment       |
| `int.enable_jobs_post_deployment`     | Manual   | Enable jobs after deployment         |
| `int.disable_pre_data_retention_jobs` | Manual   | Disable jobs before data retention   |
| `int.enable_post_data_retention_jobs` | Manual   | Enable jobs after data retention     |

### 10. Backup & Maintenance Jobs (1 job)

**Purpose**: System cleanup

| Job Name                                | Command                | Schedule     | Purpose                       |
| --------------------------------------- | ---------------------- | ------------ | ----------------------------- |
| `int.olcs.sched.remove_expired_backups` | Remove expired backups | Daily, 06:00 | Clean up old backup files     |
| `int.shd.sched.remove_expired_backups`  | Remove expired backups | Daily, 06:10 | Clean up old SHD backup files |

### 11. Search & Indexing Jobs (1 job)

**Purpose**: Search functionality maintenance

| Job Name                                | Command                | Schedule              | Purpose                     |
| --------------------------------------- | ---------------------- | --------------------- | --------------------------- |
| `int.olcs.searchdatav6.indices.rebuild` | Rebuild search indices | Daily, Mon-Fri, 12:00 | Rebuild OLCS search indices |

## Scheduling Patterns Summary

### High-Frequency Operations (24/7)

- **Every 2 minutes (9 jobs)**: Core queue processing - Mon-Fri business hours
    - General queue, disc generation/print, permits, ECMT, community licenses, TransXChange
- **Every 5-30 minutes (6 jobs)**: External consumers and payments
    - Companies House, insolvency, payment resolution

### Daily Operations by Time

#### Early Morning (00:00-08:59) - 12 jobs

- **00:45**: Permit window closure, unpaid withdrawal
- **02:00**: Interim end date enforcement
- **06:00-06:10**: Backup cleanup
- **08:00**: NI compliance, TM letters, document store reports, orphan file management

#### Morning (09:00-11:59) - 11 jobs

- **09:00**: Database operations, cleanup, system maintenance
- **09:30**: Document store processing
- **10:00**: Data extracts and reports
- **11:00**: Certificate updates

#### Afternoon (12:00-17:59) - 1 job

- **12:00**: Search index rebuilding

#### Evening (18:00-23:59) - 13 jobs

- **18:00**: Batch processing, reporting, extracts
- **18:15**: Vehicle and permit processing
- **18:30**: License processing, reminders
- **18:45**: Document processing

### Weekly Operations

- **Monday**: Heaviest day with weekly/monthly jobs starting
- **Wednesday**: Multiple reporting jobs (DVA, Companies House, document exports)
- **Friday**: Weekly government reports (TC reports, audit cleanup)

### Monthly Operations

- **5th of month**: Major export reports (International goods, PSV operator lists)
- **7th of month**: License processing (Auto CNS, PSV surrender tasks)

## Command Parameters & Technology Stack

### Primary Technologies

- **PHP/Laminas Framework**: Core application commands with 2048M memory limit
- **Shell Scripts**: Database operations and system maintenance
- **SQL Queries**: Monitoring and reporting operations
- **SSH Adapter**: SOSSSHJob2JSAdapter for remote execution
- **User Context**: nginx user for web-related operations

### Key Command Patterns

```bash
# Queue Processing
sudo -u nginx /bin/bash -c 'HOME=/tmp ENVIRONMENT_NAME=int php -d memory_limit=2048M /opt/dvsa/olcs/api/vendor/bin/laminas --container=/opt/dvsa/olcs/api/config/container-cli.php queue:process-queue --exclude [queue_types]'

# Batch Operations
sudo -u nginx /bin/bash -c 'HOME=/tmp ENVIRONMENT_NAME=int php -d memory_limit=2048M /opt/dvsa/olcs/api/vendor/bin/laminas --container=/opt/dvsa/olcs/api/config/container-cli.php batch:[operation]'

# Data Exports
sudo -u nginx /bin/bash -c 'HOME=/tmp ENVIRONMENT_NAME=int php -d memory_limit=2048M /opt/dvsa/olcs/api/vendor/bin/laminas --container=/opt/dvsa/olcs/api/config/container-cli.php batch:data-gov-uk-export --report-name=[report]'

# Shell Scripts
/mnt/data/[service]/[script].sh
/usr/local/bin/[script].sh
```

## Operational Characteristics

### Load Distribution

- **Continuous Operations**: 17 jobs (queue processing and consumers)
- **Business Hours Focus**: 29 jobs (Monday-Friday operational)
- **Off-Peak Processing**: 13 jobs (evening batch operations)
- **Periodic Operations**: 12 jobs (weekly/monthly reporting)

### Peak Processing Times

- **00:00-23:59**: Continuous queue processing (17 jobs)
- **09:00-10:00**: Morning batch window (8 jobs)
- **18:00-18:45**: Evening processing window (13 jobs)

### Environment Architecture

The system maintains consistent job structures across multiple environments:

- **Production (int/)**: Full 74-job deployment
- **Non-Production**: Scaled configurations for dev/qa/demo/ps environments
- **Legacy Systems**: Separate job chains for older WMS components

## Data Retention Process

The data retention system uses a 6-stage pipeline requiring manual coordination:

1. **Disable Pre-Data Retention Jobs**: Stop conflicting operations
2. **Pre-check**: Validate data for retention processing
3. **Populate**: Identify records for retention
4. **Delete**: Remove database records
5. **Delete Documents**: Remove associated files from document store
6. **Enable Post-Data Retention Jobs**: Resume normal operations

## Queue Type Exclusions

The general queue processor excludes specific queue types that are handled by specialized processors:

- **Companies House**: `que_typ_ch_compare`
- **Vehicle Lists**: `que_typ_create_gds_vehicle_list`, `que_typ_create_psv_vehicle_list`
- **Printing**: `que_typ_disc_printing`, `que_typ_print`, `que_typ_disc_printing_print`
- **Community Licenses**: `que_typ_create_com_lic`
- **Document Management**: `que_typ_remove_deleted_docs`
- **Permits**: `que_typ_permit_generate`, `que_typ_permit_print`
- **ECMT Processing**: `que_typ_run_ecmt_scoring`, `que_typ_accept_ecmt_scoring`
- **IRHP Processing**: `que_typ_irhp_permits_allocate`

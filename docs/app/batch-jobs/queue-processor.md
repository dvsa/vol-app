# Queue Processor (ECS Scheduler)

## Overview

The Queue Processor is a continuous service that runs frequent queue processing jobs (≤5 minute intervals) to replace overlapping AWS Batch jobs that were causing EntityManager closure errors and lags in processing time-sensitive queue items.

## How It Works

The `queue:scheduler` command runs continuously and manages:

```php
// 9 scheduled jobs with optimized intervals:
- process_queue_general (90s) - Main queue processing
- process_queue_community_licences (90s) - Community licences
- process_queue_disc_generation (90s) - Vehicle discs
- process_queue_print (90s) - Print jobs
- process_queue_permit_generation (90s) - Permits
- process_queue_ecmt_accept (90s) - ECMT scoring
- process_queue_irhp_allocate (90s) - IRHP allocation
- transxchange_consumer (90s) - Bus route data
- process_company_profile (5min) - Company profiles
```

### Key Features

- **Instant Execution**: No container startup delay unlike AWS Batch
- **Overlap Prevention**: Skips execution if previous job still running
- **Non-blocking**: Jobs run as separate processes with real-time output
- **Health Monitoring**: Writes status to `/tmp/scheduler-health.json`
- **Graceful Shutdown**: Handles SIGTERM/SIGINT properly
- **Environment Filtering**: AWS-dependent jobs disabled locally

## Local Development

The queue processor runs automatically in `docker compose up`:

- Only runs jobs marked `local_enabled: true`
- Skips AWS-dependent jobs (transxchange, company-profile)
- Provides same queue processing as production

## AWS Deployment

Deployed via Terraform as ECS service:

- Same IAM roles and network access as API service
- Uses queue-processor ECR repository
- Health checks configured for ECS task
- Runs in dev/int/prep environments currently

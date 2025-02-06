# VOL Application Low Level Design - Container Infrastructure

This document details the technical implementation of the containerized Vehicle Operator Licensing (VOL) application infrastructure.

## Application Components

The application is split into several containerized services:

### Frontend Services

1. **Selfserve** (`selfserve`)

    - Public-facing web interface
    - PHP-FPM based container
    - Includes ClamAV for document scanning

2. **Internal** (`internal`)
    - Staff-facing web interface
    - PHP-FPM based container
    - Includes ClamAV for document scanning

### Backend Services

1. **API** (`api`)

    - RESTful API service
    - PHP-FPM based container
    - Handles all data operations

2. **Search** (`search`)
    - Elasticsearch-based search service
    - Custom configuration
    - Handles search operations
    - Configured via `settings.sh` and `logstash.yaml`

### Support Services

1. **CLI** (`cli`)

    - Command-line tools container
    - Maintenance and admin tasks / batch job runs
    - Custom PHP configuration

2. **Liquibase** (`liquibase`)
    - Database migration service
    - Manages schema changes
    - To be swapped out with Doctrine Migrations soon

## Container Implementation

### Base Configuration

Each service container is defined in `infra/docker/[service]/Dockerfile` with:

```
Architecture: linux/arm64
Base Runtime: PHP-FPM (except search)
Port Exposure: 8080 (HTTP)
Health Check: /healthcheck endpoint
```

### Service-Specific Configurations

-   PHP services use custom `php.ini` and `php-fpm.conf`
-   Web services include Nginx configurations (`*.conf`)
-   Search service includes Elasticsearch configurations
-   ClamAV integration for document scanning services

## AWS Infrastructure

### ECS Implementation

The application runs on ECS with the following structure:

```
Environment
└── Service (e.g., selfserve, internal, api)
    ├── ECS Cluster
    │   └── ECS Service
    │       └── Task Definition
    │           └── Container
    ├── Target Group (Port 8080)
    └── Load Balancer Rules
```

### Task Definitions

Defined in Terraform (`infra/terraform/modules/service/ecs.tf`):

-   Resource allocation per service
-   Environment variables
-   Container definitions
-   Health check configuration

### Service Configuration

Each service includes:

```hcl
CPU: Configurable per environment
Memory: Configurable per environment
Networking: awsvpc mode
Auto-scaling: Configurable per service
Health check: HTTP on port 8080
```

## Networking Architecture

### Load Balancer Configuration

-   Application Load Balancer (ALB)
-   Target Groups:
    -   Protocol: HTTP
    -   Port: 8080
    -   Health Check:
        -   Path: /healthcheck
        -   Interval: 300s
        -   Timeout: 60s
        -   Healthy threshold: 2
        -   Unhealthy threshold: 2

### Routing

```
Internet → CloudFront → ALB → Container (8080)
```

-   Host-based routing via ALB rules
-   Service discovery via ECS DNS
-   VPC private subnets for containers

## Container Registry

### ECR Configuration

Primary repository: `054614622558.dkr.ecr.eu-west-1.amazonaws.com/vol-app/[service]`
Mirror: `ghcr.io/dvsa/vol-app/[service]`

### Image Tags

-   Git SHA for all builds
-   Semantic version for releases
-   Latest tag maintained
-   Signed images using AWS Signer

## Environment Configuration

### Environment Variables

Standard across services:

```
ENVIRONMENT_NAME: Legacy environment mapping
APP_VERSION: Container version
ELASTICACHE_URL: Cache endpoint
CDN_URL: CloudFront distribution (when enabled)
```

### Per-Environment Setup

Four distinct environments:

1. Development (DEV)
2. Integration (INT)
3. Pre-production (PREP)
4. Production (PROD)

Each with separate:

-   ECS clusters
-   Task definitions
-   Load balancer configurations
-   Security group rules

## Infrastructure as Code

### Terraform Structure

```
infra/terraform/
├── accounts/
│   ├── nonprod/
│   └── prod/
├── environments/
│   ├── dev/
│   ├── int/
│   ├── prep/
│   └── prod/
└── modules/
    ├── account/
    ├── service/
    └── remote-state/
```

### Key Modules

1. **Service Module**

    - ECS service configuration
    - Load balancer setup
    - Security groups
    - CloudWatch configuration

2. **Account Module**
    - ECR repository setup
    - Base networking
    - IAM configurations

## Deployment Pipeline

### Build Process

1. Docker image build (ARM64)
2. Image scanning (Trivy)
3. Push to ECR/GHCR
4. Image signing

### Deployment Flow

1. ECS task definition update
2. Progressive rollout
3. Health check validation
4. Traffic cutover

## Monitoring Integration

### Health Checks

-   Container health: /healthcheck endpoint
-   ALB health checks
-   ECS task health status
-   CloudWatch metrics

### Logging

-   Container logs to CloudWatch
-   Application logs
-   Load balancer access logs
-   Container Insights enabled

## Shared Resources

### Data Storage

-   RDS for persistent data
-   ElastiCache for session state
-   EFS where needed for persistent storage

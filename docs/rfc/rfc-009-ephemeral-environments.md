# RFC-009: Implementing Ephemeral Environments

## Summary

Implement the infrastructure and deployment framework to support ephemeral environments that can be created on-demand for feature branches, testing, and QA purposes, with automated provisioning and cleanup.

## Problem

The VOL application currently has fixed environments (DEV & INT in non-prod and PREP & PROD in prod accounts). This limited set of environments creates several challenges:

1. **Resource contention**: QA team members and developers must coordinate access to just two nonprod environments, leading to scheduling conflicts and work being blocked.

2. **Test isolation**: Multiple feature branches cannot be isolated for testing, increasing the risk of interference between tests.

3. **Deployment delays**: Features may be blocked from testing due to unavailable environments, slowing down the development cycle.

4. **Full pipeline requirement**: Even for small changes, all merges to main must go through the full deployment pipeline, consuming time and resources.

## Proposal

Implement a system for creating and managing ephemeral environments that:

1. Can be quickly provisioned on-demand
2. Are isolated from other environments
3. Usees resources efficiently
4. Cleans up environments automatically when no longer needed or at end of their planned rutime
5. Support feature branch testing and QA workflows

### Technical Approach

#### 1. DNS Configuration

Create dedicated Route53 zones for ephemeral environments:

```
[env-name].ephemeral.olcs.dev-dvsacloud.uk    # Non-prod environments
[env-name].ephemeral.olcs.dvsacloud.uk        # Prod environments (if needed)
```

Using a dedicated subdomain provides:

-   Clear separation between ephemeral and permanent environments
-   Simplified DNS management
-   Allows use of wildcard ssl certs

We can then configure service-specific DNS entries for each environment:

```
api.[env-name].ephemeral.olcs.dev-dvsacloud.uk
ssweb.[env-name].ephemeral.olcs.dev-dvsacloud.uk
iuweb.[env-name].ephemeral.olcs.dev-dvsacloud.uk
```

#### 2. Infrastructure Organization

The ephemeral environments could use a combination of:

**Shared Resources (per account)**:

-   VPC and subnets
-   Load balancers (with host-based routing)
-   Aurora MySQL database cluster (for cloning)
-   ElastiCache clusters
-   Cognito user pools

**Dedicated Resources (per environment)**:

-   Aurora MySQL database clone
-   ECS clusters and services
-   Target groups
-   CloudWatch log groups

#### 3. Terraform Implementation

We will create a dedicated Terraform module for ephemeral environments that:

-   Uses Terraform workspaces for state isolation
-   Parameterises environment names and configurations
-   Supports dynamic resource creation and cleanup

Key code excerpt for workspace handling:

```hcl
locals {
  is_ephemeral = terraform.workspace != "default"
  environment_name = local.is_ephemeral ? terraform.workspace : var.environment
  domain_prefix = local.is_ephemeral ? "${local.environment_name}.ephemeral" : var.environment
}
```

#### 4. Aurora MySQL Database Strategy

Given the successful outcome of the current Aurora trial, ephemeral environments will use database cloning with a single non-prod DB cluster as the foundation:

-   Create and maintain a "source cluster" (primary Aurora cluster) with anonymized data in non-prod
-   Use Aurora's clone functionality for near-instant database provisioning
-   Implement copy-on-write for storage efficiency
-   Configure serverless v2 capacity for cost optimisation

Key advantages:

-   Near-instant provisioning (seconds vs. maybe 20-30 minutes with RDS)
-   Minimal storage overhead (only changed data is stored with copy on write)
-   Independent scaling per environment
-   Cost based on actual usage

Key code excerpt for database cloning:

```hcl
resource "aws_rds_cluster" "ephemeral" {
  cluster_identifier   = "vol-${var.environment_name}"
  engine               = "aurora-mysql"

  # Clone from source cluster
  restore_type           = "copy-on-write"
  source_cluster_identifier = var.source_cluster_id

  # Cost optimisation settings
  serverlessv2_scaling_configuration {
    min_capacity = 0.5  # Minimum ACUs
    max_capacity = 2    # Maximum ACUs
  }
}
```

#### 5. Application Load Balancer Configuration

Use host-based routing for efficient ALB utilisation:

-   Single shared ALB for all ephemeral environments
-   Host-based routing rules to direct traffic
-   Wildcard SSL certificate for all subdomains

#### 6. Environment Lifecycle Management

Implement automated processes for:

-   Environment creation via GitHub workflows
-   Resource tagging for tracking and management
-   TTL-based expiration
-   Automated cleanup of expired environments

Key code excerpt for TTL tagging:

```hcl
locals {
  expiration_date = timeadd(timestamp(), "${var.ttl_days * 24}h")
}

# Tag all resources with TTL
tags = {
  Name         = "vol-${var.environment_name}"
  Environment  = var.environment_name
  TTL          = local.expiration_date
  Branch       = var.branch
  EphemeralEnv = "true"
}
```

#### 7. CI/CD Workflow Strategy

Unlike the standard progressive deployment pipeline (DEV → INT → PREP → PROD), ephemeral environments will need a different CI/CD approach with a dedicated workflow:

**Hybrid CI/CD Model**:

-   Use a dedicated workflow file (e.g., `ephemeral-cd.yaml`) targeting only ephemeral environments
-   Combine minimal automated testing for fast feedback with optional manually triggered workflows for extra validation (e.g full regression or performance suites)

**Automated Components**:

-   Commit validation (lint, unit tests, snyk etc) on every push to the feature branch
-   Automatic deployment to ephemeral environment when created/updated
-   Basic smoke tests after each deployment to verify core functionality

**Manual Trigger Components**:

-   Full regression test suite (can be triggered via workflow dispatch on github UI)
-   Performance testing when needed (triggered via workflow dispatch on github UI)
-   Other automated tests/suites

Example workflow structure:

```yaml
# Simplified example of ephemeral-cd.yaml
name: Ephemeral Environment CI/CD

on:
    # Automatic triggers for feature branches
    push:
        branches:
            - "feature/**"

    # Manual triggers with configurable options
    workflow_dispatch:
        inputs:
            test_suite:
                description: "Test suite to run"
                required: true
                default: "smoke"
                type: choice
                options:
                    - smoke
                    - regression
                    - performance
                    - security
```

This approach balances speed (automatic basic testing) with thoroughness (manual deep testing when desired), optimising resource usage while maintaining quality control. The ephemeral environment itself is the deployment target, without needing to progress through the traditional pipeline stages.

### Limitations (TBD)

1. Maximum of 10 concurrent ephemeral environments to manage costs
2. Default TTL of 7 days per environment
3. Are all batch jobs required for every ephemeral environment?
4. Limited to the non-prod AWS account initially

## Implementation

The implementation will be phased:

### Phase 1: Infrastructure Foundation

-   Set up DNS and wildcard certificates
-   Create shared network infrastructure
-   Implement Aurora source cluster setup
-   Develop Terraform module for ephemeral environments

### Phase 2: CI/CD Integration

-   Create GitHub workflow for environment management
-   Implement PR integration
-   Develop environment cleanup automation
-   Set up monitoring and alerts

### Phase 3: Database Management

-   Implement source cluster refresh process
-   Set up database cloning process

### Phase 4: Developer Experience

-   Create environment management dashboard
-   Extend CLI tools
-   Create comprehensive documentation
-   Train development and QA teams

## Implementation Tickets

It is expected that these tickets will be needed to implement ephemeral environments:

### Infrastructure Setup

**Create DNS zones for ephemeral environments**  
This task involves setting up dedicated Route53 hosted zones for the ephemeral environment domain pattern. We'll need to create wildcard SSL certificates using ACM to secure all subdomains under the ephemeral domain.

**Set up shared load balancer for ephemeral environments**  
Configure an Application Load Balancer that will serve all ephemeral environments through host-based routing. This shared infrastructure approach minimises costs while providing the ability to route traffic to the appropriate environment based on the hostname. Includes creating listener rules with host-header conditions and implementing path-based routing for each service (API, selfserve, internal).

**Create Aurora source cluster database**  
Deploy the primary Aurora MySQL cluster that will serve as the foundation for all ephemeral environments. This involves configuring the cluster with appropriate instance types, setting up parameter groups, configuring backups, and establishing connectivity from the VPC. This source cluster will be the template from which all ephemeral environment databases are cloned.

**Review and update data anonymisation processes**  
Our existing database anonymisation scripts and utilities may not be fit for purpose in an Aurora based system, or may require an overhaul to be able to run them in the most appropriate way, ticket(s) for this may be require, or a spike at least.

**Create Terraform module for ephemeral environments**  
Develop a reusable Terraform module that encapsulates all the infrastructure components needed for an ephemeral environment. This module will define the ECS services, target groups, Aurora database clone, security groups, IAM roles, and other required resources. It must be parameterised to allow for dynamic environment naming and configuration variants.

**Set up Terraform workspace management**  
Implement a workflow for managing Terraform workspaces that isolate state for each ephemeral environment. This includes creating automation scripts for workspace creation/deletion, establishing naming conventions, and configuring remote state storage. This ensures that environments can be created and destroyed independently without affecting other environments.

### CI/CD Integration

**Create GitHub workflow for ephemeral environment creation**  
Develop a GitHub Actions workflow that provisions ephemeral environments through Terraform. This workflow will handle authentication to AWS, workspace management, and Terraform execution. It should provide clear output logs, handle errors gracefully, and include post-deployment validation to ensure the environment is properly provisioned.

**Implement PR-based environment triggers**  
Add functionality to automatically trigger environment creation based on PR events and labels. This involves creating GitHub webhook handlers, implementing logic to parse PR metadata, and initiating the environment creation workflow. The system should update the PR with environment details once provisioning is complete.

**Develop environment cleanup Lambda function**  
Create an AWS Lambda function that regularly scans for expired ephemeral environments and triggers cleanup. This function will query resources with TTL tags, determine if they've expired, and initiate the teardown process. It should include proper error handling, notifications, and logging to ensure reliable cleanup operations.

**Set up monitoring and alerting for ephemeral resources**  
Configure CloudWatch dashboards and alerts specifically for monitoring ephemeral environments. This includes setting up metrics collection, creating custom dashboards for resource utilisation, establishing appropriate alarm thresholds, and configuring notification channels. The monitoring system should provide visibility into both individual environment health and overall resource usage. Perhaps consider integration with Slack environment status posts to summarise what ephemerals are running at key points during a day.

**Implement ephemeral environment CI/CD workflow**  
Create a dedicated ephemeral-cd.yaml workflow with the hybrid automated/manual testing approach. This differs from the standard progressive deployment pipeline by targeting only the ephemeral environment and including both automated testing components (linting, unit tests, smoke tests) and manual trigger options for more intensive testing suites (regression, performance, security).

### Database Management

**Implement source cluster database refresh process**  
Develop an automated process to periodically update the source cluster database with fresh, anonymized data. This will involve creating scheduler jobs, implementing data extraction and transformation logic, and ensuring minimal downtime during refreshes. The process should include validation steps and rollback mechanisms in case of failures.

**Develop database cloning process**  
Implement the technical process for quickly creating Aurora database clones from the source cluster. This process must integrate with the environment provisioning workflow, implement proper naming conventions, configure appropriate scaling parameters, and ensure security controls are applied. Includes optimising the clone creation process for speed and efficiency.

**Implement database resource optimisation**  
Configure Aurora serverless v2 scaling parameters to optimise costs while maintaining performance. This includes setting appropriate minimum and maximum capacity units, establishing scaling policies based on usage patterns.

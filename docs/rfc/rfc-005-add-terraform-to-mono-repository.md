# RFC-005: Add Terraform to Mono-repository

## Summary

This RFC proposes incorporating Terraform into the mono repository to manage infrastructure as code, initially focusing on container-related AWS resources.

## Problem

Historically, Terraform has been managed in a separate repository, leading to several challenges:

### Rollback

The Terraform repository lacks automated versioning alignment with application code, complicating understanding of infrastructure state at specific points in time. This creates challenges during rollbacks or deploying older application versions for testing, as it necessitates running two independent workflows in a specific sequence, contradicting the principles of continuous delivery (CD).

### Duplication & Maintenance

Maintaining separate repositories necessitates duplicating GitHub Action workflows across them to apply changes to both application and infrastructure. Each repository must also check out code from the other, typically requiring the application code to utilize a Personal Access Token (PAT) for accessing private infrastructure code, adding to maintenance overhead.

## Proposal

Building on the proposal from RFC-001, Terraform will be integrated into the mono repository to manage infrastructure as code, initially focusing on container-related resources.

### Terraform Directory Structure

Terraform files will reside in the `infra/terraform` directory, organized as follows:

```js
|-- `terraform/`
|       |-- `accounts/`
|       |       |-- `nonprod/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- `prod/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |-- `environments/`
|       |       |-- `dev/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- `int/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- `prep/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- `prod/`
|       |       |       |-- `backend.tf`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- ...
|       |-- `modules/`
|       |       |-- `account/`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- `service/`
|       |       |       |-- `main.tf`
|       |       |       |-- `outputs.tf`
|       |       |       |-- `provider.tf`
|       |       |       |-- `variables.tf`
|       |       |       |-- ...
|       |       |-- ...
```

### Scope

This RFC will cover resources created with the following Terraform modules:

-   https://github.com/terraform-aws-modules/terraform-aws-ecr
-   https://github.com/terraform-aws-modules/terraform-aws-ecs

Any resources that support the above work can be included without requiring a new RFC. Subsequent RFCs can be created for larger collections of resources, such as networking or databases.

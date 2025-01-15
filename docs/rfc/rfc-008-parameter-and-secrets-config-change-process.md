# RFC: Parameter and Secret Management Changes

## Background

Our Laminas app uses AWS Parameter Store and Secrets Manager for configuration management. We've experienced runtime failures during releases etc when parameter placeholders don't exist in AWS. This creates a need for a defined process to manage parameter and secret changes in vol-app.

## Proposed Solution

### Process for Parameter Store Management

When adding or modifying parameters in Laminas config files:

1. Developer identifies need for new parameter
2. Developer MUST:
    - Create a branch in `vol-terraform` repository
    - Add parameter values to appropriate files in `/etc/`
3. Developer MUST create two linked PRs:
    - `vol-terraform` PR with parameter additions
    - `vol-app` PR with application changes to config
4. Both PRs MUST reference each other in their descriptions
5. The `vol-terraform` PR should be merged before or at the same time as the application PR

## Notes

The vol-terraform etc folder contains the files that define parameters. There are two group files which contain values shared by all environments in an account, then per-environment files for environment-specific values. Please use the least-specific file possible for your changes.

## File Structure Example

```
/etc/
├── env_eu-west-1_dev.tfvars     # Dev-specific values
├── env_eu-west-1_pp.tfvars      # PP-specific values
├── group_nonprod.tfvars         # Shared non-prod values
└── group_prod.tfvars            # Shared prod values
```

### Process for Secret Management

As we should not store secret values in a repository, AWS Secrets Manager is used. If a developer needs a new secret it is proposed that they:

1. Raises a ticket for Infrastructure team, linked to the ticket they are working on with:
    - Secret placeholder name
    - Description of purpose
    - Required environments
    - Where the value can be found / who is responsible for updating it in future etc
2. Infrastructure team will:
    - Create the secret in AWS Secrets Manager
    - Notify the developer upon completion
3. Developer can then merge their application changes

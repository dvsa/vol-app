---
sidebar_position: 30
---

# AWS Secrets and Parameters Sync

Automatically syncs AWS SSM Parameters and Secrets Manager values into local PHP configuration files.

## Prerequisites

:::warning MFA Required
Your shell session must have **valid AWS credentials with MFA** for the VOL nonprod account before running the sync.
:::

## How It Works

1. Parses PHP config files using AST for precise updates
2. Fetches values from AWS (cached per sync session)
3. Updates config values at exact character positions
4. Validates PHP syntax before saving
5. Creates backup and rolls back on any error

## Usage

```bash
npm run refresh
```

Select "Sync AWS secrets and parameters" and choose environment (DEV/INT).

## Configuration

Mappings are defined in `packages/local-refresh/src/actions/SyncAwsSecretsAndParameters/mappings.json`.

### Structure

```json
[
    {
        "service": "api",
        "basePath": "app/api",
        "placeholders": [
            { "key": "ENV", "value": "${environment.toUpperCase()}" },
            { "key": "env", "value": "${environment}" },
            { "key": "SERVICE", "value": "${service.toUpperCase()}" },
            { "key": "service", "value": "${service}" }
        ],
        "files": [
            {
                "path": "config/autoload/local.php",
                "mappings": [
                    // Individual configuration mappings here
                ]
            }
        ]
    }
]
```

### Placeholders

Dynamic values resolved at runtime:

- `${environment}` - Selected environment ("dev", "int")
- `${environment.toUpperCase()}` - Uppercase environment ("DEV", "INT")
- `${service}` - Service name from config
- `${service.toUpperCase()}` - Uppercase service name

### Mapping Fields

| Field        | Required | Description                                         |
| ------------ | -------- | --------------------------------------------------- |
| `configPath` | Yes      | Array path in PHP config (e.g., `["db", "host"]`)   |
| `awsPath`    | Yes      | AWS resource path (supports placeholders)           |
| `type`       | Yes      | `"parameter"` (SSM) or `"secret"` (Secrets Manager) |
| `secretKey`  | No       | JSON key to extract from secret                     |
| `prepend`    | No       | String to prepend to value                          |
| `append`     | No       | String to append to value                           |

## Examples

**SSM Parameter:**

```json
{
    "configPath": ["db", "host"],
    "awsPath": "/applicationparams/{env}/db_host",
    "type": "parameter"
}
```

**Plain Text Secret:**

```json
{
    "configPath": ["api", "token"],
    "awsPath": "DEVAPP{ENV}-API-TOKEN",
    "type": "secret"
}
```

_Treats entire secret value as a string._

**JSON Secret with Key:**

```json
{
    "configPath": ["auth", "cognito", "secret"],
    "awsPath": "DEVAPP{ENV}-SM-{SERVICE}",
    "type": "secret",
    "secretKey": "cognito_client_secret"
}
```

_Extracts specific key from JSON secret._

**With Transformation:**

```json
{
    "configPath": ["api", "auth"],
    "awsPath": "/params/{env}/api_key",
    "type": "parameter",
    "prepend": "Bearer "
}
```

## Debug Mode

```bash
DEBUG=*SyncAwsSecretsAndParameters* npm run refresh
```

Shows detailed AWS calls, cache hits, and config updates.

## Required IAM Permissions

```json
{
    "Effect": "Allow",
    "Action": ["ssm:GetParameter", "secretsmanager:GetSecretValue", "sts:GetCallerIdentity"],
    "Resource": ["arn:aws:ssm:*:*:parameter/applicationparams/*", "arn:aws:secretsmanager:*:*:secret:DEVAPP*"]
}
```

## Common Errors

| Error                              | Cause                                  | Solution                                     |
| ---------------------------------- | -------------------------------------- | -------------------------------------------- |
| `Config path X not found`          | `configPath` doesn't exist in PHP file | Verify path exists in target file            |
| `Parameter/Secret does not exist`  | AWS resource missing                   | Check resource exists in AWS console         |
| `Access denied`                    | Insufficient IAM permissions           | Verify credentials have required permissions |
| `Key 'X' not found in secret JSON` | JSON key missing from secret           | Check secret structure in AWS                |
| `Secret is not valid JSON`         | Malformed JSON in secret               | Fix JSON formatting in AWS secret            |

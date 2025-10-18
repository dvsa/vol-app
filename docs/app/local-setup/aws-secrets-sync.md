---
sidebar_position: 30
---

# AWS Secrets and Parameters Sync

The AWS Secrets and Parameters sync system automatically fetches values from AWS Systems Manager Parameter Store and AWS Secrets Manager, then updates your local PHP configuration files. This ensures your local development environment has the correct configuration values without manually copying secrets.

## How It Works

The sync system uses a sophisticated approach to reliably update PHP configuration files:

1. **AST-Based Parsing**: Uses a PHP parser to build an Abstract Syntax Tree (AST) for reliable config file analysis
2. **Precise Position Replacement**: AST provides exact character positions for surgical string replacement
3. **Smart Caching**: AWS API calls are cached to avoid duplicate requests during a single sync
4. **Type Safety**: Supports both plain parameters and JSON secrets with specific key extraction
5. **Path Validation**: Only existing configuration paths are updated, with clear error reporting for missing paths

## Configuration File

The sync behavior is controlled by `packages/local-refresh/src/config/config-mappings.json`.

### Basic Structure

```json
[
    {
        "service": "api",
        "basePath": "app/api",
        "placeholders": {
            "ENV": "${environment.toUpperCase()}",
            "env": "${environment}",
            "SERVICE": "${service.toUpperCase()}",
            "service": "${service}"
        },
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

### Service Configuration

- **`service`**: Service identifier (e.g., "api", "selfserve", "internal")
- **`basePath`**: Relative path from project root to the service directory
- **`placeholders`**: Dynamic placeholder definitions for use in AWS paths
- **`files`**: Array of files to process for this service

### Placeholder System

Placeholders allow dynamic AWS path generation based on the selected environment:

```json
"placeholders": {
  "ENV": "${environment.toUpperCase()}",    // "dev" → "DEV"
  "env": "${environment}",                  // "dev" → "dev"
  "SERVICE": "${service.toUpperCase()}",    // "api" → "API"
  "service": "${service}"                   // "api" → "api"
}
```

**Supported Transformations:**

- `toUpperCase()`: Convert to uppercase
- `toLowerCase()`: Convert to lowercase

### Mapping Configuration

Each mapping defines how an AWS resource maps to a PHP configuration value:

#### Required Fields

- **`configPath`**: Array representing the nested path in the PHP config
- **`awsPath`**: AWS resource path (Parameter Store path or Secrets Manager name)
- **`type`**: Either `"parameter"` or `"secret"`

#### Optional Fields

- **`secretKey`**: For JSON secrets, the specific key to extract
- **`prepend`**: String to add before the value
- **`append`**: String to add after the value

## Mapping Examples

### Simple Parameter

```json
{
    "configPath": ["dvsa_address_service", "client", "base_uri"],
    "awsPath": "/applicationparams/{env}/address_service_url",
    "type": "parameter"
}
```

Maps to PHP:

```php
'dvsa_address_service' => [
  'client' => [
    'base_uri' => 'https://address-service.example.com'
  ]
]
```

### JSON Secret with Key Extraction

```json
{
    "configPath": ["auth", "adapters", "cognito", "clientSecret"],
    "awsPath": "DEVAPP{ENV}-BASE-SM-APPLICATION-{SERVICE}",
    "type": "secret",
    "secretKey": "aws_cognito_client_secret"
}
```

This extracts the `aws_cognito_client_secret` key from a JSON secret and maps to:

```php
'auth' => [
  'adapters' => [
    'cognito' => [
      'clientSecret' => 'extracted-secret-value'
    ]
  ]
]
```

### Value Transformation

```json
{
    "configPath": ["companies_house", "http", "curloptions", "CURLOPT_USERPWD"],
    "awsPath": "DEVAPP{ENV}-BASE-SM-APPLICATION-{SERVICE}",
    "type": "secret",
    "secretKey": "olcs_companieshouseapikey",
    "append": ":"
}
```

Maps to PHP with the `:` appended:

```php
'companies_house' => [
  'http' => [
    'curloptions' => [
      CURLOPT_USERPWD => 'api-key-value:'
    ]
  ]
]
```

## Supported PHP Structures

The system handles various PHP array key formats:

### String Keys

```php
'string_key' => 'value'
"double_quoted_key" => "value"
```

### Numeric Keys

```php
10005 => 'value'
```

### PHP Constants

```php
CURLOPT_USERPWD => 'value'
CURLOPT_PROXY => 'value'
```

### Deeply Nested Arrays

```php
'service' => [
  'config' => [
    'nested' => [
      'deep_key' => 'value'
    ]
  ]
]
```

## Usage

### Running the Sync

The sync is part of the main refresh command:

```bash
npm run refresh
```

The interactive tool will prompt you to:

1. Select environment (DEV/INT)
2. Confirm AWS credentials
3. Process all configured mappings

### Debug Mode

For troubleshooting, enable debug output:

```bash
DEBUG=*SyncAwsSecretsAndParameters* npm run refresh
```

This shows detailed information about:

- AST traversal and precise position calculation
- AWS API calls and caching behavior
- Success/failure reasons for each mapping
- Character position replacements (e.g., "chars 10650-10652")
- PHP syntax validation results

## AWS Setup Requirements

### Credentials

Ensure AWS credentials are configured using one of:

- AWS CLI profile (`aws configure`)
- Environment variables (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_SESSION_TOKEN`)
- IAM roles (for EC2/ECS environments)

:::warning
You must be authenticated with the AWS VOL `nonprod` account to access the required resources.
:::

### Required IAM Permissions

Your AWS credentials need the following permissions:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": ["ssm:GetParameter", "secretsmanager:GetSecretValue", "sts:GetCallerIdentity"],
            "Resource": ["arn:aws:ssm:*:*:parameter/applicationparams/*", "arn:aws:secretsmanager:*:*:secret:DEVAPP*"]
        }
    ]
}
```

## Troubleshooting

### Common Error Messages

#### "Config key not found in PHP array structure"

- The `configPath` doesn't match the actual PHP file structure
- Check the target PHP file has the expected nested array structure
- Verify path spelling and case sensitivity

#### "Access denied - insufficient permissions"

- AWS credentials lack required IAM permissions
- Ensure you're authenticated with the correct AWS account
- Check the resource ARN matches your AWS paths

#### "Parameter/Secret not found"

- AWS resource doesn't exist for the specified environment
- Verify the `awsPath` is correct after placeholder substitution
- Check the resource exists in AWS console

#### "PHP parsing failed"

- Target PHP file has syntax errors
- File isn't valid PHP or has corrupted structure
- Check the file can be parsed by running `php -l filename.php`

### Success Monitoring

The tool reports completion statistics:

```
Files processed: 1
Values updated: 46
Values failed: 0
AWS API calls: 31 (cached to avoid duplicates)
```

:::tip
Aim for 100% success rate. Any failures should be investigated and resolved by checking the configuration paths and AWS resources.
:::

## Maintaining Configuration

### Adding New Mappings

1. Identify the PHP configuration path in your local.php file
2. Determine the corresponding AWS resource (parameter or secret)
3. Add the mapping to `config-mappings.json`
4. Test with debug mode to verify the mapping works
5. Ensure the AWS resource exists in all target environments

### Updating Existing Mappings

1. Verify the PHP structure still matches your `configPath`
2. Test AWS resource accessibility
3. Use debug mode to troubleshoot any issues
4. Update both development and production environments as needed

### PHP Configuration Changes

When the PHP config structure changes:

1. Update corresponding `configPath` arrays in the mappings
2. Run a test sync to ensure no failures
3. Update documentation if new patterns are introduced

This system provides robust, reliable configuration sync using AST-based parsing and precise position replacement. With comprehensive error handling and debugging capabilities, it ensures your local development environment stays perfectly in sync with AWS-managed configuration values while maintaining 100% PHP syntax integrity.

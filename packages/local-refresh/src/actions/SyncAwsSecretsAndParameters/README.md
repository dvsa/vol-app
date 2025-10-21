# SyncAwsSecretsAndParameters Action

This action synchronizes AWS Secrets Manager secrets and SSM Parameter Store parameters into local PHP configuration files.

## Structure

```
SyncAwsSecretsAndParameters/
├── index.ts          # Main action implementation
├── types.ts          # TypeScript type definitions
├── config.ts         # Configuration loader with validation
├── mappings.json     # AWS mapping configuration
└── README.md         # This file
```

## How It Works

1. **Prompts** the user to select an environment (dev/int)
2. **Validates** AWS credentials using STS
3. **Loads** `mappings.json` which defines:
    - Services to process
    - File paths relative to service base
    - Mappings from AWS paths to config paths
4. **Fetches** values from AWS using `Aws`
5. **Updates** PHP config files using `LaminasConfig`
6. **Validates** PHP syntax after updates

## Configuration Format

The `mappings.json` file defines an array of service configurations:

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
                    {
                        "configPath": ["db", "connection", "host"],
                        "awsPath": "/applicationparams/{env}/db_host",
                        "type": "parameter"
                    },
                    {
                        "configPath": ["api", "key"],
                        "awsPath": "DEVAPP{ENV}-SECRET",
                        "type": "secret",
                        "secretKey": "api_key",
                        "prepend": "Bearer ",
                        "append": ""
                    }
                ]
            }
        ]
    }
]
```

### Fields

- **service**: Service identifier (for logging)
- **basePath**: Path relative to project root (6 levels up from action)
- **placeholders**: Dynamic values resolved at runtime
- **files**: Array of config files to update
- **configPath**: Array representing nested PHP array keys
- **awsPath**: Path to AWS resource (supports placeholder substitution)
- **type**: Either "parameter" (SSM) or "secret" (Secrets Manager)
- **secretKey**: For JSON secrets, the key to extract
- **prepend/append**: Optional strings to add to the value

## Dependencies

- **Aws**: Handles AWS API calls with caching
- **LaminasConfig**: Handles PHP config file manipulation

## Usage

This action is automatically discovered by the main index.ts when placed in the actions directory as either:

- Single file: `actions/ActionName.ts`
- Folder module: `actions/ActionName/index.ts` (this pattern)

The folder pattern allows for:

- Action-specific configuration files
- Helper modules
- Better organization for complex actions

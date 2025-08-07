# Mock GOV.UK Sign In Service

A mock OAuth 2.0/OpenID Connect provider that simulates GOV.UK Sign In for local development and testing.

## Security Notice

**⚠️ This is a MOCK service for testing only**

The following files contain intentionally committed test keys that have **NO security implications**:

- `mock-private.pem` - RSA private key for mock JWT signing
- `mock-public.pem` - RSA public key for mock JWT verification
- `mock-ec-private.pem` - EC private key for mock coreIdentityJWT
- `mock-ec-public.pem` - EC public key for mock coreIdentityJWT verification

These keys are:

- ✅ Intentionally insecure and committed to the repository
- ✅ Used ONLY for local testing and development
- ✅ Never used in production environments
- ✅ Safe to be detected by security scanners (configured in `.gitleaks.toml`)

**DO NOT** use this service or these keys for any production or security-sensitive purposes.

## Features

- ✅ Full OAuth 2.0 authorization code flow
- ✅ OpenID Connect discovery endpoint
- ✅ Configurable validation modes (permissive, standard, strict)
- ✅ Test user scenarios for different outcomes
- ✅ GOV.UK Sign In compatible response format
- ✅ HTML login page for Selenium testing
- ✅ Zero configuration needed in permissive mode

## Quick Start

The service is included in the main Docker Compose stack:

```bash
# Start all services including mock sign-in
docker-compose up -d

# View mock service info
curl http://localhost:8090/mock-info

# View login page
open http://localhost:8090/authorize?client_id=test&redirect_uri=http://localhost/callback&state=test
```

## Validation Modes

### Permissive Mode (Default)

- Accepts any credentials
- Auto-fixes missing OAuth parameters
- Doesn't validate JWT signatures
- Perfect for local development

### Standard Mode

- Validates parameters exist
- Logs warnings but continues
- Good for integration testing

### Strict Mode

- Full OAuth validation
- Rejects invalid requests
- Closest to real service behavior

Set via environment variable:

```bash
VALIDATION_MODE=permissive # default
VALIDATION_MODE=standard
VALIDATION_MODE=strict
```

## Test Users

### Predefined Scenarios

| Email                   | Outcome   | Description                 |
| ----------------------- | --------- | --------------------------- |
| `test.success@mock.gov` | Success   | P2 identity verification    |
| `test.denied@mock.gov`  | Denied    | Access denied error         |
| `test.p1only@mock.gov`  | P1 Level  | Insufficient identity level |
| `test.cancel@mock.gov`  | Cancelled | User cancellation           |
| `test.timeout@mock.gov` | Timeout   | Session timeout             |

### Dynamic Users

Create users with specific data:

- `john.smith.1990-01-15@mock.gov` - Name: John Smith, DOB: 1990-01-15
- `jane.doe@mock.gov` - Name: Jane Doe, DOB: 1990-01-01 (default)

Any other email will be accepted as a successful P2 verification.

## Endpoints

| Endpoint                                | Description              |
| --------------------------------------- | ------------------------ |
| `GET /.well-known/openid-configuration` | OpenID discovery         |
| `GET /authorize`                        | Login page               |
| `POST /authorize`                       | Process login            |
| `POST /token`                           | Exchange code for tokens |
| `GET /userinfo`                         | Get user information     |
| `GET /jwks`                             | Public keys              |
| `GET /health`                           | Health check             |
| `GET /mock-info`                        | Service information      |

## Configuration

### Environment Variables

```bash
# Server
PORT=8080
NODE_ENV=development

# Validation
VALIDATION_MODE=permissive

# OAuth
ISSUER_URL=http://mock-govuk-signin:8080

# Logging
LOG_LEVEL=debug

# JWT (auto-generated if not provided)
JWT_PRIVATE_KEY=
JWT_PUBLIC_KEY=
```

### VOL App Configuration

The VOL app is configured to use the mock service by default. To use real GOV.UK Sign In:

```bash
# Set environment variable in VOL API container
GOVUK_SIGNIN_DISCOVERY_URL=https://oidc.integration.account.gov.uk/.well-known/openid-configuration
```

## Development

### Local Development

```bash
cd app/mock-govuk-signin
npm install
npm run dev
```

### Building

```bash
npm run build
```

### Testing

```bash
npm test
```

## OAuth Flow

1. VOL redirects to `/authorize` with OAuth parameters
2. User enters test email on login page
3. Mock service validates based on email scenario
4. Redirects back to VOL with auth code (or error)
5. VOL exchanges code for tokens at `/token`
6. VOL fetches user info from `/userinfo`

## Response Format

The mock service returns GOV.UK Sign In compatible responses:

```json
{
    "https://vocab.account.gov.uk/v1/coreIdentityJWT": "<jwt>",
    "https://vocab.account.gov.uk/v1/coreIdentityJWT:decoded": {
        "vot": "P2",
        "vc": {
            "credentialSubject": {
                "name": [
                    {
                        "validUntil": null,
                        "nameParts": [
                            { "type": "GivenName", "value": "John" },
                            { "type": "FamilyName", "value": "Smith" }
                        ]
                    }
                ],
                "birthDate": [{ "value": "1990-01-15" }]
            }
        }
    }
}
```

## Troubleshooting

### Service not responding

```bash
# Check if running
docker ps | grep mock-govuk-signin

# Check logs
docker logs mock-govuk-signin

# Restart service
docker-compose restart mock-govuk-signin
```

### OAuth errors

- Check validation mode - use `permissive` for debugging
- View `/mock-info` endpoint for current configuration
- Check browser console for redirect URLs

### VOL not using mock service

- Ensure `GOVUK_SIGNIN_DISCOVERY_URL` is not set
- Check VOL API configuration
- Restart VOL API container

## Security Note

⚠️ This is a mock service for development and testing only. Never use in production or with real credentials.

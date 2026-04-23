---
sidebar_position: 35
---

# Mock GOV.UK Sign In

A local-only mock of the [GOV.UK Sign In](https://www.sign-in.service.gov.uk/) OIDC API. It lets developers (and the VFT functional test pack) walk the GOV.UK identity verification journey end-to-end without depending on the real third-party tenant.

The full reference — endpoints, validation modes, environment variables, security model — lives in [`app/mock-govuk-signin/README.md`](https://github.com/dvsa/vol-app/blob/main/app/mock-govuk-signin/README.md). This page is a short orientation for using it locally.

## Overview

- Express + TypeScript service that implements the OIDC endpoints VOL consumes.
- Issues real (mock-signed) ID tokens, access tokens, and `coreIdentityJWT`s using committed test PEMs.
- Five named test users plus a dynamic email scheme — see [Test users](#test-users) below.
- Runs in-process inside an AWS Lambda Runtime Interface Emulator container, so the local image is bit-for-bit close to the eventual Lambda deployment target.

## Local development

The service is part of the standard compose stack:

```bash
docker compose up -d mock-govuk-signin
```

It exposes itself to nginx-proxy as `http://mocksignin.local.olcs.dev-dvsacloud.uk`. Quick smoke tests:

```bash
curl -fsS http://mocksignin.local.olcs.dev-dvsacloud.uk/health
curl -fsS http://mocksignin.local.olcs.dev-dvsacloud.uk/.well-known/openid-configuration | jq .
curl -fsS http://mocksignin.local.olcs.dev-dvsacloud.uk/jwks | jq .
```

### Wiring VOL at the mock

Two `local.php.dist` files ship commented-out blocks that point VOL at the mock instead of the real GOV.UK Sign In integration tenant. Copy the relevant `local.php.dist` to `local.php` and uncomment:

| File                                           | What to uncomment                                                                                                                        |
| ---------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| `app/api/config/autoload/local.php.dist`       | `govuk_account` — overrides `discovery_endpoint`, `core_identity_did_document_url`, `expected_core_identity_issuer`, `client_id`.        |
| `app/selfserve/config/autoload/local.php.dist` | `govukaccount-redirect` — switches `referrer_ends_with` to an array that allows the mock's hostname through the redirect referrer check. |

Restart the `api` and `selfserve` containers after editing.

## Endpoints

| Method | Path                                | Purpose                                                                 |
| ------ | ----------------------------------- | ----------------------------------------------------------------------- |
| GET    | `/.well-known/openid-configuration` | OIDC discovery document.                                                |
| GET    | `/.well-known/did.json`             | DID document used to verify `coreIdentityJWT` signatures.               |
| GET    | `/jwks`                             | JSON Web Key Set for ID/access token verification.                      |
| GET    | `/authorize`                        | Login page (HTML form listing the test users).                          |
| POST   | `/authorize`                        | Login submission — issues an auth code and redirects to `redirect_uri`. |
| POST   | `/token`                            | OAuth code → ID/access token exchange.                                  |
| GET    | `/userinfo`                         | OIDC userinfo (returned in GOV.UK Sign In's claim shape).               |
| GET    | `/health`                           | Liveness probe.                                                         |
| GET    | `/mock-info`                        | Debug — dumps current config and the test user list.                    |

## Test users

Five predefined emails cover the journeys VOL needs to handle:

| Email                   | Outcome                                      |
| ----------------------- | -------------------------------------------- |
| `test.success@mock.gov` | Successful P2 verification.                  |
| `test.denied@mock.gov`  | `access_denied` from the IdP.                |
| `test.p1only@mock.gov`  | P1 only — should be treated as insufficient. |
| `test.cancel@mock.gov`  | User cancellation.                           |
| `test.timeout@mock.gov` | Session timeout.                             |

Any other address ending `@mock.gov` is accepted as a successful P2:

- `firstname.lastname.YYYY-MM-DD@mock.gov` — dynamic user with the chosen DOB.
- `firstname.lastname@mock.gov` — dynamic user, DOB defaults to `1990-01-01`.

Hit `GET /mock-info` for the live list.

## Security notice

The mock keys in `app/mock-govuk-signin/mock-*.pem` are **intentionally insecure** test keys. They are committed to make local setup zero-friction, allowlisted in `.gitleaks.toml`, and must never be used in any environment that handles real users. Read the security notice at the top of [`app/mock-govuk-signin/README.md`](https://github.com/dvsa/vol-app/blob/main/app/mock-govuk-signin/README.md) before reusing any of this code outside local development.

## Deployment

There is no AWS deployment yet — the mock currently only runs in the local docker-compose stack. The Dockerfile uses the AWS Lambda RIE base on purpose so a future deploy can reuse the same image as a Lambda function.

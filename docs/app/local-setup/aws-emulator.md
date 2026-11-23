---
sidebar_position: 40
---

# AWS Emulator

The `aws-emulator` service in `compose.yaml` runs [AWS Emulator](https://github.com/aws/aws-emulator), which serves AWS service APIs (S3, SQS, SNS, Secrets Manager, SSM and others) over HTTPS on `127.0.0.1:9297`. Use it to try AWS calls without credentials, MFA or a shared environment. Nothing you do there reaches real AWS.

- **Telemetry is off.** The emulator collects usage telemetry by default; the image and the compose service both set `EMULATOR_TELEMETRY=off`. Its start log confirms it: `docker compose logs aws-emulator | grep telemetry`.
- **State is in memory only.** Stopping or restarting the container empties it, and there is no other reset. Each start also creates a new certificate and new access keys.
- **One account and one Region.** Every request runs in account `000000000000`.
- **No access control.** The emulator accepts every request signed with its own keys, so don't use it to test IAM, bucket or KMS key policies. This is also why its port is published on the host's loopback only.

## Starting it

It starts with the rest of the stack (`docker compose up -d`). On its own:

```bash
docker compose up -d --wait aws-emulator
```

The image installs the latest emulator release when it's built, and the build stops unless the package is signed with AWS's published key. To upgrade, rebuild it without the cache:

```bash
docker compose build --pull --no-cache aws-emulator && docker compose up -d --wait aws-emulator
```

## Using it from your machine

`infra/docker/aws-emulator/aws-local` runs a command against the emulator:

```bash
infra/docker/aws-emulator/aws-local aws sts get-caller-identity   # Account: 000000000000
infra/docker/aws-emulator/aws-local aws s3 mb s3://my-bucket
infra/docker/aws-emulator/aws-local aws s3 ls
```

Each time, it fetches the running emulator's certificate and access keys into a temporary folder of its own, points the AWS settings of that one command at them, and removes the folder when the command finishes. It keeps your own AWS credentials, `AWS_*` environment variables and `~/.aws` profiles out of the command, so the command can't reach real AWS, and it leaves your shell unchanged. It works for anything that uses an AWS SDK or the AWS CLI, such as scripts or `terraform`.

If the account shown is anything other than `000000000000`, the command reached real AWS: stop and check your environment.

## Using it from another container

The VOL applications don't use the emulator yet. If you point a container at it, note that the emulator's certificate is only valid for `localhost` and `127.0.0.1`, so connecting to `https://aws-emulator:9297` fails certificate verification. Never turn verification off. Instead, connect to `https://localhost:9297` and have curl send that connection to the `aws-emulator` container, for example with `curl --connect-to localhost:9297:aws-emulator:9297`. In PHP, and in the AWS SDK's `http` option, the same setting is `CURLOPT_CONNECT_TO`.

## Troubleshooting

- **`The AWS Emulator is not running`** from `aws-local`: start it with `docker compose up -d --wait aws-emulator`. If it stops again, `docker compose logs aws-emulator` says why.
- **`AccessDenied` or `SignatureDoesNotMatch`**: the request was signed with keys from an earlier run, or with your real AWS keys. Run it through `aws-local`.
- **`NotImplemented` (HTTP 501)**: the installed emulator doesn't cover that operation yet. Rebuild to get the latest release, which may cover it.
- **The build fails with `not signed with AWS's key`**: the downloaded package didn't pass the signature check. Don't work around it. If AWS has changed its signing key, the [AWS Emulator user guide](https://docs.aws.amazon.com/emulator/latest/userguide/getting-started-linux.html) gives the new fingerprint, which goes in `infra/docker/aws-emulator/Dockerfile`.

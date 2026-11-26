---
sidebar_position: 40
---

# AWS Emulator

The `aws-emulator` service in `compose.yaml` runs [AWS Emulator](https://github.com/aws/aws-emulator), which serves AWS service APIs (S3, SQS, SNS, Secrets Manager, SSM and others) over HTTPS on `127.0.0.1:9297`. Use it to try AWS calls without credentials, MFA or a shared environment. Nothing you do there reaches real AWS.

- **Telemetry is off.** The emulator collects usage telemetry by default; the image and the compose service both set `EMULATOR_TELEMETRY=off`. Its start log confirms it: `docker compose logs aws-emulator | grep telemetry`.
- **State is in memory only.** Stopping or restarting the container empties it, and there is no other reset. Each start also creates a new certificate and new access keys.
- **One account and one Region.** Every request runs in account `000000000000`. `aws-local` uses `eu-west-1`; the emulator accepts any Region but doesn't keep them apart.
- **No access control.** The emulator accepts every request signed with its own keys, so don't use it to test IAM, bucket or KMS key policies. This is also why its port is published on the host's loopback only, so other machines can't reach it. The other containers in the stack can: port 9297 is open to them, and so are the extra ports the emulator opens for resource endpoints, which don't check signatures at all.

## Starting it

It starts with the rest of the stack (`docker compose up -d`). On its own:

```bash
docker compose up -d --wait aws-emulator
```

The image installs the latest emulator release when it's built, and the build stops unless the package is signed with AWS's published key. To upgrade, rebuild it without the cache:

```bash
docker compose build --pull --no-cache aws-emulator && docker compose up -d --wait aws-emulator
```

AWS releases a new version several times a day and fixes security problems in the latest release only, so the image isn't pinned. If a release breaks something, install an earlier one: put it in the root `.env` file, then rebuild.

```bash
echo AWS_EMULATOR_VERSION=1.0.588 >> .env
docker compose build aws-emulator && docker compose up -d --wait aws-emulator
```

AWS keeps only recent releases; [`versions.json`](https://downloads.aws.com/aws-emulator/packages/aws-emulator/LTS/versions.json) lists the ones it still serves. Remove the line again once a newer release has fixed the problem.

## Using it from your machine

`infra/docker/aws-emulator/aws-local` runs a command against the emulator:

```bash
infra/docker/aws-emulator/aws-local aws sts get-caller-identity   # Account: 000000000000
infra/docker/aws-emulator/aws-local aws s3 mb s3://my-bucket
infra/docker/aws-emulator/aws-local aws s3 ls
```

Each time, it fetches the running emulator's certificate and access keys into a temporary folder of its own, points the AWS settings of that one command at them, and removes the folder when the command finishes. It leaves your shell unchanged, and it keeps your own AWS credentials, `AWS_*` environment variables and `~/.aws` profiles out of the command, so nothing the command sends is signed with your real credentials. It also exempts the emulator from any proxy you have set.

It works for tools that read the standard `AWS_ENDPOINT_URL` and `AWS_CA_BUNDLE` settings, such as the AWS CLI and the JavaScript, Python and Go SDKs. A tool too old to read `AWS_ENDPOINT_URL` sends its requests to real AWS instead. They are signed with the emulator's keys, so AWS rejects them, but if the account shown is anything other than `000000000000`, stop and check your environment. Some tools need more:

- **PHP:** the AWS SDK for PHP ignores `AWS_CA_BUNDLE`, so requests fail with `cURL error 60`. Pass the certificate yourself: `new S3Client([..., 'http' => ['verify' => getenv('AWS_CA_BUNDLE')]])`.
- **Terraform:** data sources and most resources work, such as `aws_sqs_queue`. `aws_s3_bucket` does not: AWS provider 6 reads bucket tags through S3 Control, which puts the account number in front of the host name (`000000000000.127.0.0.1`), and that name doesn't exist.
- **Java and .NET:** they don't read a PEM file. The emulator also writes a `truststore.p12`, but `aws-local` doesn't fetch it.

## Using it from another container

The VOL applications don't use the emulator yet, and connecting another container to it isn't set up. Whoever does it needs to solve three things:

- **The certificate and keys.** They're new on every start and live only in the `aws-emulator` volume, readable only by the emulator's user (uid 1000). The VOL containers run as other users, so they can't simply mount the volume.
- **The host name.** The certificate is only valid for `localhost`, `127.0.0.1` and `::1`, so connecting to `https://aws-emulator:9297` fails certificate verification. Never turn verification off. Instead, connect to `https://localhost:9297` and have curl send that connection to the `aws-emulator` container, for example with `curl --connect-to localhost:9297:aws-emulator:9297`. In PHP, and in the AWS SDK's `http` option, the same setting is `CURLOPT_CONNECT_TO`.
- **S3 bucket addresses.** With a `localhost` endpoint the SDK puts the bucket in the host name (`my-bucket.localhost`), which neither that setting nor the certificate covers. Set `use_path_style_endpoint` to `true`.

## Troubleshooting

- **`Could not fetch the AWS Emulator's certificate and keys`** from `aws-local`: Docker's own message follows it. `service "aws-emulator" is not running` means start it with `docker compose up -d --wait aws-emulator`; if it stops again, `docker compose logs aws-emulator` says why. In a Git worktree, Compose uses a different project name, so it doesn't find an emulator started from your main checkout: run `aws-local` from that checkout instead.
- **`AccessDenied` or `SignatureDoesNotMatch`**: the request was signed with keys from an earlier run, or with your real AWS keys. Run it through `aws-local`.
- **`NotImplemented` (HTTP 501)**: the installed emulator doesn't cover that operation yet. Rebuild to get the latest release, which may cover it (and remove any `AWS_EMULATOR_VERSION` from `.env` first).
- **The build fails with `Could not download AWS Emulator`**: the `AWS_EMULATOR_VERSION` in `.env` isn't a release AWS serves (it keeps only recent ones). Pick one from `versions.json`, or remove the line to go back to the latest.
- **The build fails with `not signed with AWS's key`**: the downloaded package didn't pass the signature check. Don't work around it. If AWS has changed its signing key, the [AWS Emulator user guide](https://docs.aws.amazon.com/emulator/latest/userguide/getting-started-linux.html) gives the new fingerprint, which goes in `infra/docker/aws-emulator/Dockerfile`.

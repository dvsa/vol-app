# AWS Emulator (https://github.com/aws/aws-emulator): local AWS service APIs over HTTPS on port 9297.
# No image is published, so this one is built from the Linux package. It installs the latest release
# (AWS ships security fixes in the latest release only); to upgrade, rebuild without the cache:
#   docker compose build --pull --no-cache aws-emulator
# If a release breaks something, set AWS_EMULATOR_VERSION (for example 1.0.588) in the root .env file
# and rebuild to install that release instead. AWS lists the releases it still serves in
# https://downloads.aws.com/aws-emulator/packages/aws-emulator/LTS/versions.json
FROM public.ecr.aws/amazonlinux/amazonlinux:2023-minimal

# Set by BuildKit; the legacy builder leaves it empty, so the build falls back to uname -m.
ARG TARGETARCH
ARG AWS_EMULATOR_VERSION=latest
# Fingerprint of the key AWS signs the package with, from the AWS Emulator user guide (Getting
# started > Linux > "To verify the package before you install it"). The build stops unless the
# package's signature is valid and made with this key. If AWS changes its key, take the new
# fingerprint from that page.
ARG SIGNING_KEY_FINGERPRINT=98BCF336A3BE53B734E967C52E5BF49BD940F624

RUN arch="${TARGETARCH:-$(uname -m)}" \
    && case "$arch" in \
         arm64|aarch64) a=aarch64 ;; \
         amd64|x86_64) a=x86_64 ;; \
         *) echo "AWS Emulator has no package for the $arch architecture, only arm64 and amd64" >&2; exit 1 ;; \
       esac \
    && base=https://downloads.aws.com/aws-emulator/packages/aws-emulator/LTS \
    && case "$AWS_EMULATOR_VERSION" in \
         latest) url="$base/latest/aws-emulator-$a.rpm" ;; \
         [0-9]*.[0-9]*.[0-9]*) url="$base/aws-emulator-$AWS_EMULATOR_VERSION-1-$a.rpm" ;; \
         *) echo "AWS_EMULATOR_VERSION must be latest or a release such as 1.0.588, not $AWS_EMULATOR_VERSION" >&2; \
            exit 1 ;; \
       esac \
    && d="$(mktemp -d)" \
    && if ! curl -fsSLo "$d/package.rpm" "$url"; then \
         echo "Could not download AWS Emulator $AWS_EMULATOR_VERSION from $url. AWS keeps only recent" \
           "releases; $base/versions.json lists them" >&2; \
         exit 1; \
       fi \
    && curl -fsSLo "$d/package.rpm.sig" "$url.sig" \
    && curl -fsSLo "$d/key.asc" https://downloads.aws.com/keys/gpg/current.asc \
    && export GNUPGHOME="$d" \
    && gpg --batch --no-autostart --quiet --import "$d/key.asc" \
    && gpg --batch --no-autostart --status-file "$d/status" --verify "$d/package.rpm.sig" "$d/package.rpm" \
    && if ! grep -q "^\[GNUPG:\] VALIDSIG .* $SIGNING_KEY_FINGERPRINT\$" "$d/status"; then \
         echo "The AWS Emulator package is not signed with AWS's key $SIGNING_KEY_FINGERPRINT" >&2; exit 1; \
       fi \
    && rpm --import "$d/key.asc" \
    && rpm -i --define '_pkgverify_level signature' "$d/package.rpm" \
    && rm -rf "$d" \
    && mkdir -p /home/emulator/.aws/emulator \
    && chown -R 1000:1000 /home/emulator

USER 1000:1000

ENV HOME=/home/emulator
ENV PATH=/opt/aws-emulator/bin:$PATH
# Usage telemetry is on by default upstream. Off here, so no build of this image sends any.
ENV EMULATOR_TELEMETRY=off

EXPOSE 9297

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
  CMD ["curl", "-sf", "--cacert", "/home/emulator/.aws/emulator/9297/ca.pem", "https://127.0.0.1:9297/__admin/health"]

# 0.0.0.0 inside the container so Docker can forward the port; compose publishes it on the host's
# loopback only.
ENTRYPOINT ["aws-emulator", "-b", "0.0.0.0"]

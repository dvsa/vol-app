# AWS Emulator (https://github.com/aws/aws-emulator): local AWS service APIs over HTTPS on port 9297.
# No image is published, so this one is built from the Linux package. It always installs the latest
# release; to upgrade, rebuild without the cache: docker compose build --pull --no-cache aws-emulator
FROM public.ecr.aws/amazonlinux/amazonlinux:2023-minimal

ARG TARGETARCH
# Fingerprint of the key AWS signs the package with, from the AWS Emulator user guide (Getting
# started > Linux > "To verify the package before you install it"). The build stops unless the
# package's signature is valid and made with this key. If AWS changes its key, take the new
# fingerprint from that page.
ARG SIGNING_KEY_FINGERPRINT=98BCF336A3BE53B734E967C52E5BF49BD940F624

RUN case "$TARGETARCH" in arm64) a=aarch64 ;; amd64) a=x86_64 ;; *) exit 1 ;; esac \
    && d="$(mktemp -d)" \
    && url="https://downloads.aws.com/aws-emulator/packages/aws-emulator/LTS/latest/aws-emulator-$a.rpm" \
    && curl -fsSLo "$d/package.rpm" "$url" \
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

USER 1000

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

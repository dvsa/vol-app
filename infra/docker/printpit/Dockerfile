FROM alpine:3.22 AS builder

# Set shell for proper pipe error handling
SHELL ["/bin/ash", "-eo", "pipefail", "-c"]

# Install build dependencies
RUN apk add --no-cache \
    go \
    git \
    nodejs \
    npm \
    make

# Install pnpm
RUN npm install -g pnpm

# Build FileBrowser from source
ENV FILEBROWSER_VERSION=v2.42.5
WORKDIR /build
RUN git clone --depth 1 --branch ${FILEBROWSER_VERSION} https://github.com/filebrowser/filebrowser.git . && \
    cd frontend && \
    npm install && \
    npm run build && \
    cd .. && \
    go build -ldflags "-s -w" -o filebrowser .

FROM alpine:3.22

# Set shell for proper pipe error handling
SHELL ["/bin/ash", "-eo", "pipefail", "-c"]

# Install required packages
# cups-pdf is in edge/testing repository (available for both x86_64 and aarch64)
# hadolint ignore=DL3018
RUN echo "https://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories && \
    echo "https://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories && \
    apk add --no-cache \
    cups \
    cups-pdf \
    cups-client \
    cups-filters \
    ghostscript \
    supervisor \
    curl \
    bash \
    tzdata \
    pcre2 \
    && rm -rf /var/cache/apk/*

# Copy FileBrowser binary from builder
COPY --from=builder /build/filebrowser /usr/local/bin/filebrowser
RUN chmod +x /usr/local/bin/filebrowser

# Create necessary directories
RUN mkdir -p /var/spool/cups-pdf \
    && chmod 777 /var/spool/cups-pdf \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/run/cups \
    && mkdir -p /var/lib/filebrowser \
    && chmod 755 /var/lib/filebrowser

# Copy configuration files
COPY cupsd.conf /etc/cups/cupsd.conf
COPY cups-pdf.conf /etc/cups/cups-pdf.conf
COPY cleanup.sh /usr/local/bin/cleanup.sh
COPY entrypoint.sh /entrypoint.sh
COPY supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chmod +x /usr/local/bin/cleanup.sh /entrypoint.sh \
    && chmod 755 /etc/cups \
    && chmod 644 /etc/cups/*.conf \
    && chmod 700 /usr/lib/cups/backend/cups-pdf \
    && chown root:root /usr/lib/cups/backend/cups-pdf

# Environment variables
ENV CLEANUP_HOURS=6 \
    CUPS_ADMIN_USER=admin \
    CUPS_ADMIN_PASSWORD=admin \
    TZ=Europe/London

# Expose ports
EXPOSE 631 8631

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=60s \
    CMD curl -f http://localhost:631/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]

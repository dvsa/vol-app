#!/usr/bin/env bash
#
# Basic HTTP latency bench for local VOL stack.
# See infra/bench/README.md for caveats — this is NOT a load test.
#
# Usage:
#   infra/bench/bench.sh              # uses infra/bench/urls.txt (or urls.example.txt as fallback)
#   infra/bench/bench.sh path/to/urls.txt
#
# For authenticated benchmarks, log in first and export COOKIE_JAR:
#   curl -c /tmp/vol-cookies.txt -X POST -d 'username=usr20&password=Password1' \
#     http://iuweb.local.olcs.dev-dvsacloud.uk/auth/login/
#   COOKIE_JAR=/tmp/vol-cookies.txt infra/bench/bench.sh
#
# Tune via env vars:
#   RUNS=20      # samples per URL after warmup (default 20)
#   WARMUP=3     # warmup requests to discard (default 3)

set -euo pipefail

RUNS="${RUNS:-20}"
WARMUP="${WARMUP:-3}"
COOKIE_JAR="${COOKIE_JAR:-}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
URL_FILE="${1:-${SCRIPT_DIR}/urls.txt}"
if [[ ! -f "$URL_FILE" ]]; then
  URL_FILE="${SCRIPT_DIR}/urls.example.txt"
  echo "(no urls.txt found — using urls.example.txt)" >&2
fi

CURL_OPTS=(-s -o /dev/null --max-time 30)
if [[ -n "$COOKIE_JAR" ]]; then
  CURL_OPTS+=(-b "$COOKIE_JAR")
fi

printf '%-60s %10s %10s %10s %10s %10s\n' "URL" "min" "median" "p95" "max" "mean"
printf -- '-%.0s' {1..115}; echo

while IFS= read -r url; do
  # Skip comments and blanks
  [[ -z "$url" || "$url" =~ ^# ]] && continue

  # Warmup
  for ((i = 0; i < WARMUP; i++)); do
    curl "${CURL_OPTS[@]}" "$url" || true
  done

  # Measure
  samples=()
  for ((i = 0; i < RUNS; i++)); do
    t=$(curl "${CURL_OPTS[@]}" -w '%{time_total}' "$url" || echo "0")
    samples+=("$t")
  done

  # Stats via awk (sorted)
  stats=$(printf '%s\n' "${samples[@]}" | sort -n | awk -v n="$RUNS" '
    { a[NR] = $1; sum += $1 }
    END {
      p95_idx = int(n * 0.95)
      if (p95_idx < 1) p95_idx = 1
      median = (n % 2) ? a[(n+1)/2] : (a[n/2] + a[n/2+1]) / 2
      printf "%.3f %.3f %.3f %.3f %.3f", a[1], median, a[p95_idx], a[n], sum/n
    }')

  printf '%-60s %s\n' "${url:0:60}" "$(echo "$stats" | awk '{printf "%10s %10s %10s %10s %10s", $1, $2, $3, $4, $5}')"
done < "$URL_FILE"

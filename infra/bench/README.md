# Local dev benchmark

A small set of shell scripts for measuring local Docker dev performance — useful for comparing before/after when changing OPcache, autoloader, or filesystem-mount settings.

> [!IMPORTANT] > **This is a basic benchmark, not a load test.**
>
> It measures single-client request latency with `curl` against an already-running local stack. It does not simulate concurrent users, does not exercise complex workflows, does not isolate CPU/IO contention from other processes on your host, and does not produce confidence intervals.
>
> Treat the numbers as a rough comparison only — useful for "is this change a 10% win or a 2× win?", not for capacity planning or production SLO work.

## Scripts

| Script             | Purpose                                                                  |
| ------------------ | ------------------------------------------------------------------------ |
| `bench.sh`         | Times HTTP requests against a list of URLs and reports min/median/p95.   |
| `opcache-stats.sh` | Prints OPcache, APCu, and realpath cache status from inside a container. |
| `urls.example.txt` | Sample URL list. Copy to `urls.txt` and customise.                       |

## Quick start

```bash
# One-off run against the defaults
npm run bench

# Or invoke directly with a custom URL list
infra/bench/bench.sh infra/bench/urls.txt
```

## Inspecting OPcache state

After the stack is up and you've hit a few pages, see what OPcache actually cached:

```bash
infra/bench/opcache-stats.sh api          # or selfserve / internal / cli
```

Healthy numbers after warmup:

- **Hit ratio** > 99%
- **Cached scripts** in the low thousands (not pinned at `opcache.max_accelerated_files`)
- **Used memory** well below the `opcache.memory_consumption` ceiling
- **Wasted memory** < ~10% of total

If hit ratio is poor or wasted memory is high, the cache is thrashing — bump `opcache.memory_consumption` in `infra/docker/<service>/php.ini`.

## Before/after methodology

To compare two configurations cleanly:

```bash
# 1. Baseline
git stash                              # set aside any uncommitted perf changes
docker compose down
docker compose build api selfserve internal cli
docker compose up -d
sleep 30                               # let everything warm up
infra/bench/bench.sh > /tmp/before.txt

# 2. With your changes
git stash pop
docker compose down
docker compose build api selfserve internal cli
docker compose up -d
sleep 30
infra/bench/bench.sh > /tmp/after.txt

# 3. Compare
diff -y /tmp/before.txt /tmp/after.txt
```

## Caveats that will distort results

- **First request after a container restart** is always slow (cold OPcache, cold realpath cache, fresh DB connections). The script does three warmup requests per URL, but if your stack is genuinely cold you may want to run it twice.
- **macOS filesystem cache** — bind-mount reads are cached by macOS itself. Identical files re-read shortly after the first access will be much faster than truly cold reads.
- **Background load** on the host — close browser tabs with heavy JS, pause Spotlight indexing, and don't run docker builds in parallel.
- **MySQL query plan cache** warms up over the first few requests to a page.
- **Login state** matters — most VOL pages require auth. The example script does an unauthenticated bench against public endpoints. For authenticated benchmarking, log in first with curl and pass the cookie jar (see comments in `bench.sh`).

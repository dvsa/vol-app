#!/usr/bin/env bash
#
# Prints OPcache, APCu, and realpath cache state from inside a running VOL container.
# Usage: infra/bench/opcache-stats.sh [service]    # default: api
#
# Run AFTER hitting a few pages so caches are populated.

set -euo pipefail

SERVICE="${1:-api}"

docker compose exec -T "$SERVICE" php -r '
$s = opcache_get_status(false);
if (!$s || empty($s["opcache_enabled"])) {
  echo "OPcache: DISABLED\n";
  exit(1);
}

$mem = $s["memory_usage"];
$stats = $s["opcache_statistics"];
$totalMem = $mem["used_memory"] + $mem["free_memory"] + $mem["wasted_memory"];

printf("OPcache\n");
printf("  enabled:           yes\n");
printf("  cached scripts:    %d / %d (limit)\n",
       $stats["num_cached_scripts"], $stats["max_cached_keys"]);
printf("  memory used:       %.1f MB / %.1f MB\n",
       $mem["used_memory"]/1048576, $totalMem/1048576);
printf("  memory wasted:     %.1f MB (%.1f%%)\n",
       $mem["wasted_memory"]/1048576, $mem["current_wasted_percentage"]);
printf("  hit ratio:         %.2f%% (%d hits / %d misses)\n",
       $stats["opcache_hit_rate"], $stats["hits"], $stats["misses"]);
printf("  restarts (OOM):    %d\n", $stats["oom_restarts"]);
printf("  restarts (hash):   %d\n", $stats["hash_restarts"]);

echo "\n";

if (function_exists("apcu_cache_info")) {
  $a = apcu_cache_info(true);
  printf("APCu\n");
  printf("  entries:           %d\n", $a["num_entries"]);
  printf("  memory used:       %.1f MB\n", $a["mem_size"]/1048576);
  printf("  hits / misses:     %d / %d\n", $a["num_hits"], $a["num_misses"]);
  $total = $a["num_hits"] + $a["num_misses"];
  printf("  hit ratio:         %.2f%%\n", $total > 0 ? ($a["num_hits"]/$total*100) : 0);
  echo "\n";
}

$rp = realpath_cache_size();
printf("Realpath cache\n");
printf("  size:              %.1f KB / %s (limit)\n",
       $rp/1024, ini_get("realpath_cache_size"));
printf("  entries:           %d\n", count(realpath_cache_get()));
'

# Checking what an upgrade changed

Upgrading anything in `app/cdn` regenerates the CSS and JS that both frontends load, and
nothing here is covered by a test. The usual review question — _what did that actually
change?_ — has historically had no answer, so upgrades either got waved through or did not
happen at all.

`tools/asset-snapshot.mjs` answers it. Snapshot the built assets before, upgrade, rebuild,
compare.

## The routine

```bash
cd app/cdn

npm run build:production
npm run assets:save -- .before.json

npm audit fix                                # or npm install x@y, or a dependabot branch
npm run build:production
npm run assets:save -- .after.json

npm run assets:compare -- .before.json .after.json
```

Output is per-file: added, removed, changed, with byte deltas. `compare` with a single
argument diffs against the current build instead of a second snapshot.

Snapshot files are scratch — write them outside the repo or to names git ignores. There is
deliberately no committed baseline: assets legitimately change whenever the source does, so
a checked-in one would be stale within a week and would train people to regenerate it
without looking. This is a review aid, not a gate.

## Reproducibility

`build:production` cleans before it compiles — `compile` begins with `pre-clean`, so no
separate step is needed. Consecutive builds of identical source produce byte-identical
output, with one exception noted below.

## What gets normalised

Datestamps, cache-busting query strings, line endings and trailing whitespace are stripped
before hashing, because they change on every build regardless of dependency versions. If a
new source of per-build churn appears, extend `normalise()` — do not start excluding whole
files, or the tool stops answering the question it exists to answer.

## Known quirks, as at 2026-07-30

- **`styles/print.css.map` appears intermittently.** Across three consecutive
  `build:production` runs on unchanged source it was absent, then present, then absent.
  Nothing in the config obviously produces it: `sass:prod` sets `sourceMap: false`, and the
  `postcss` targets that do write maps cover only `internal` and `selfserve`, not `print`.
  Cause not established — treat it as noise in a diff, and note that it is the one genuine
  instability in the build.
- **Production builds emit CSS sourcemaps.** `sass:prod` and `uglify:prod` both set
  `sourceMap: false`, but the `postcss` targets for `internal` and `selfserve` set
  `map: { inline: false }`, which writes `internal.css.map` and `selfserve.css.map`
  regardless. `print.css` has no postcss target, which is why it behaves differently.
  Whether shipping those maps to the CDN is intended has not been decided — they expose the
  original SCSS structure.

#!/usr/bin/env node
/**
 * Snapshot and compare the built CDN assets.
 *
 * Upgrading anything in this package changes generated CSS and JS, and nothing here is
 * covered by a test — so the usual question at review time ("what did that actually
 * change?") has had no answer. This gives it one.
 *
 *   node tools/asset-snapshot.mjs save    <file>            snapshot the current build
 *   node tools/asset-snapshot.mjs compare <before> [after]  diff two snapshots
 *
 * Typical use around an upgrade:
 *
 *   npm run build:production && npm run assets:save -- .before.json
 *   npm install some-package@latest
 *   npm run build:production && npm run assets:save -- .after.json
 *   npm run assets:compare -- .before.json .after.json
 *
 * A snapshot records, per file, its size and the hash of its *normalised* content. It is
 * a review aid rather than a pass/fail gate: an upgrade legitimately changes output, and
 * the point is to see how much and where, not to forbid it.
 */

import { createHash } from "node:crypto";
import { readFileSync, writeFileSync, existsSync, readdirSync, statSync } from "node:fs";
import { join, relative, extname } from "node:path";

// Build output only. public/index.html is hand-maintained and tracked in git, so it is
// not generated and does not belong in a build snapshot.
const ROOTS = ["public/assets", "public/js", "public/styles", "public/images"];

const TEXT = new Set([".js", ".css", ".map", ".svg", ".json", ".html"]);

/**
 * Remove the parts that change on every build regardless of dependency versions.
 *
 * Without this, every snapshot differs from every other one and the tool reports noise
 * instead of signal. Extend this — do not start ignoring whole files — when a new source
 * of per-build churn appears.
 */
function normalise(buffer, ext) {
  if (!TEXT.has(ext)) return buffer;

  let text = buffer.toString("utf8");

  // Build banners and datestamps, e.g. "/*! project 2026-07-30 */" or "Generated on ...".
  text = text.replace(/\d{4}-\d{2}-\d{2}(?:[T ]\d{2}:\d{2}(?::\d{2})?)?/g, "<DATE>");
  // Cache-busting query strings and fingerprints in url() and import paths.
  text = text.replace(/\?(?:v|rev|cb|t)=[A-Za-z0-9._-]+/g, "?<CACHEBUST>");
  // Line endings, so a tool switching platform conventions is not reported as a rewrite.
  text = text.replace(/\r\n/g, "\n");
  // Trailing whitespace, which minifier versions disagree about harmlessly.
  text = text.replace(/[ \t]+$/gm, "");

  return Buffer.from(text, "utf8");
}

function* walk(dir) {
  if (!existsSync(dir)) return;
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    const stat = statSync(full);
    if (stat.isDirectory()) yield* walk(full);
    else yield full;
  }
}

function snapshot() {
  const files = {};

  for (const root of ROOTS) {
    for (const path of walk(root)) {
      const rel = relative("public", path);
      const raw = readFileSync(path);
      const ext = extname(path);
      files[rel] = {
        bytes: raw.length,
        hash: createHash("sha256").update(normalise(raw, ext)).digest("hex").slice(0, 16),
      };
    }
  }

  return { files: Object.fromEntries(Object.entries(files).sort(([a], [b]) => a.localeCompare(b))) };
}

function save(target) {
  const snap = snapshot();
  const count = Object.keys(snap.files).length;

  if (count === 0) {
    console.error("No built assets found. Run a build first (npm run build:production).");
    process.exit(1);
  }

  writeFileSync(target, JSON.stringify(snap, null, 2) + "\n");
  console.log(`Saved ${count} files to ${target}`);
}

function compare(beforePath, afterPath) {
  const before = JSON.parse(readFileSync(beforePath, "utf8")).files;
  const after = afterPath ? JSON.parse(readFileSync(afterPath, "utf8")).files : snapshot().files;

  const names = [...new Set([...Object.keys(before), ...Object.keys(after)])].sort();

  const added = [];
  const removed = [];
  const changed = [];

  for (const name of names) {
    const a = before[name];
    const b = after[name];
    if (!a) added.push(name);
    else if (!b) removed.push(name);
    else if (a.hash !== b.hash) changed.push({ name, from: a.bytes, to: b.bytes });
  }

  const report = (label, items, render) => {
    if (items.length === 0) return;
    console.log(`\n${label} (${items.length})`);
    for (const item of items) console.log("  " + render(item));
  };

  report("ADDED", added, (n) => n);
  report("REMOVED", removed, (n) => n);
  report("CHANGED", changed, ({ name, from, to }) => {
    const delta = to - from;
    const pct = from === 0 ? "" : ` ${((delta / from) * 100).toFixed(1)}%`;
    return `${name}  ${from} -> ${to} bytes (${delta >= 0 ? "+" : ""}${delta}${pct})`;
  });

  const unchanged = names.length - added.length - removed.length - changed.length;
  console.log(`\n${unchanged} unchanged, ${changed.length} changed, ${added.length} added, ${removed.length} removed.`);

  if (added.length || removed.length) {
    console.log(
      "\nA file appearing or disappearing is the change worth looking at hardest: it usually\n" +
        "means a build step stopped running rather than that its output moved.",
    );
  }
}

const [command, ...args] = process.argv.slice(2);

switch (command) {
  case "save":
    save(args[0] ?? "asset-snapshot.json");
    break;
  case "compare":
    if (!args[0]) {
      console.error("Usage: asset-snapshot.mjs compare <before.json> [after.json]");
      process.exit(1);
    }
    compare(args[0], args[1]);
    break;
  default:
    console.error("Usage: asset-snapshot.mjs save <file> | compare <before> [after]");
    process.exit(1);
}

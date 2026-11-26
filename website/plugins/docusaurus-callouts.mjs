// Makes Docusaurus-style callouts (`:::note`, `:::tip`, `:::info`, `:::warning`, `:::danger`,
// `:::caution`) render in docmd as they did under Docusaurus. docmd 0.10 accepts most of them,
// but its migration guide glosses over two differences, which this rewrites before each page is
// parsed:
//
// - `:::tip` is docmd's tooltip container, so a tip comes out as an unstyled span. It is
//   rewritten to docmd's own `::: callout tip`.
// - docmd takes only the first word of an unquoted title as the title (and the second as a
//   link), so `:::warning MFA Required` is titled "MFA". Even quoted, a title loses any
//   `word=value` or `word:value` inside it. The title is passed as `title:"..."`, which docmd
//   reads whole.
//
// Callouts may be indented inside list items. Fenced code blocks are left alone. If docmd
// changes to handle these itself, this and its entry in docmd.config.mjs can be deleted.

import { fenceTracker } from "../markdown-fences.mjs";

export const plugin = {
  name: "vol-docusaurus-callouts",
  version: "1.0.0",
  capabilities: ["build"],
};

const CALLOUT = /^(\s*):::[ \t]*(note|tip|info|warning|danger|caution)(?:[ \t]+(.*?))?[ \t]*$/;

// docmd accepts "...", '...' or “...” and has no escapes, so use a pair the title doesn't contain.
function titleArgument(title) {
  if (/^["'“«]/.test(title)) {
    return `title:${title}`;
  }
  const quotes = [
    ['"', '"'],
    ["'", "'"],
    ["“", "”"],
  ].find(([, close]) => !title.includes(close));
  return quotes ? `title:${quotes[0]}${title}${quotes[1]}` : title;
}

function rewrite(line) {
  const match = line.match(CALLOUT);
  if (!match) {
    return line;
  }
  const [, indent, type, title] = match;
  const opener = type === "tip" ? "::: callout tip" : `:::${type}`;
  return title ? `${indent}${opener} ${titleArgument(title)}` : `${indent}${opener}`;
}

// Lines are rejoined with LF whatever they were split on. markdown-it converts CRLF to LF anyway.
export function onBeforeParse(source) {
  const inCode = fenceTracker();
  return source
    .split(/\r?\n/)
    .map((line) => (inCode(line) ? line : rewrite(line)))
    .join("\n");
}

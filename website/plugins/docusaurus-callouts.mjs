// Makes Docusaurus-style callouts (`:::note`, `:::tip`, `:::info`, `:::warning`, `:::danger`,
// `:::caution`) render in docmd as they did under Docusaurus. docmd 0.10 accepts most of them,
// but its migration guide glosses over two differences, which this rewrites before each page is
// parsed:
//
// - `:::tip` is docmd's tooltip container, so a tip comes out as an unstyled span. It is
//   rewritten to docmd's own `::: callout tip`.
// - docmd takes only the first word of an unquoted title as the title (and the second as a
//   link), so `:::warning MFA Required` is titled "MFA". The title is quoted.
//
// Callouts may be indented inside list items. Fenced code blocks are left alone. If docmd
// changes to handle these itself, this and its entry in docmd.config.mjs can be deleted.

export const plugin = {
  name: "vol-docusaurus-callouts",
  version: "1.0.0",
  capabilities: ["build"],
};

const CALLOUT = /^(\s*):::[ \t]*(note|tip|info|warning|danger|caution)(?:[ \t]+(.*?))?[ \t]*$/;

function quoteTitle(title) {
  if (/^["'“«]/.test(title)) {
    return title;
  }
  if (!title.includes('"')) {
    return `"${title}"`;
  }
  return title.includes("'") ? title : `'${title}'`;
}

function rewrite(line) {
  const match = line.match(CALLOUT);
  if (!match) {
    return line;
  }
  const [, indent, type, title] = match;
  const opener = type === "tip" ? "::: callout tip" : `:::${type}`;
  return title ? `${indent}${opener} ${quoteTitle(title)}` : `${indent}${opener}`;
}

export function onBeforeParse(source) {
  let fence = null;
  return source
    .split("\n")
    .map((line) => {
      const marker = line.match(/^\s*(`{3,}|~{3,})/);
      if (marker) {
        if (fence === null) {
          fence = marker[1][0];
        } else if (marker[1][0] === fence) {
          fence = null;
        }
        return line;
      }
      return fence === null ? rewrite(line) : line;
    })
    .join("\n");
}

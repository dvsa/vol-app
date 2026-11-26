// Tells whether each line of a Markdown file, read in order, is part of a fenced code block, so the
// sidebar builder and the callout plugin leave code examples alone. Follows CommonMark: a fence
// opens with three or more backticks or tildes, and closes on a line holding only the same
// character, at least as many times. So a four-backtick example can contain a triple-backtick one.
// Indentation isn't checked, because fences inside list items are indented.

const FENCE = /^\s*(`{3,}|~{3,})(.*)$/;

export function fenceTracker() {
  let open = null;
  return function inCode(line) {
    const marker = line.match(FENCE);
    if (open === null) {
      // A backtick fence's info string can't contain a backtick, or it's inline code.
      if (marker && !(marker[1][0] === "`" && marker[2].includes("`"))) {
        open = marker[1];
        return true;
      }
      return false;
    }
    if (marker && marker[1][0] === open[0] && marker[1].length >= open.length && marker[2].trim() === "") {
      open = null;
    }
    return true;
  };
}

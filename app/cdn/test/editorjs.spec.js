// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const url = require("node:url");

/**
 * The EditorJS component keeps the value that gets POSTed in a hidden input, refreshed
 * only by EditorJS's debounced onChange and an async save(). Anything that reads that
 * input immediately after a keystroke — an AJAX save button, a form submit — can send
 * the previous content while still reporting success.
 *
 * These run against the real EditorJS library through the fixture, so they fail if the
 * component's timing contract regresses, not merely if a mock changes.
 */

const FIXTURE = url.pathToFileURL(path.join(__dirname, "fixtures", "editorjs-harness.html")).href;

/** The hidden input's value, parsed back into the text of its first block. */
async function hiddenInputText(page, name) {
  return page.evaluate((inputName) => {
    const input = document.querySelector(`input[name='${inputName}']`);
    try {
      return JSON.parse(input.value).blocks[0].data.text;
    } catch {
      return null;
    }
  }, name);
}

async function typeIntoEditor(page, editorId, text) {
  const block = page.locator(`#${editorId} .ce-paragraph`).first();
  await expect(block).toHaveAttribute("contenteditable", "true");
  await block.click();
  await page.keyboard.press("Control+End");
  await block.pressSequentially(text);
  await expect(block).toContainText(text);
}

test.beforeEach(async ({ page }) => {
  await page.goto(FIXTURE);
  // Both editors are initialised before any test types into them.
  await expect(page.locator("#editor-section .ce-paragraph")).toHaveAttribute("contenteditable", "true");
  await expect(page.locator("#editor-variant .ce-paragraph")).toHaveAttribute("contenteditable", "true");
});

test("flushEditors makes the typed content readable immediately", async ({ page }) => {
  await typeIntoEditor(page, "editor-section", " Added by the caseworker.");

  // Read the moment typing stops, exactly as a save button handler would.
  await page.evaluate(() => window.OLCS.editorjsFlush());

  expect(await hiddenInputText(page, "sectionContent")).toContain("Added by the caseworker.");
});

test("flushEditors resolves even when no editors are on the page", async ({ page }) => {
  await page.setContent("<p>no editors here</p>");
  await page.addScriptTag({
    path: path.join(__dirname, "..", "assets", "_js", "components", "editorjs.js"),
  });

  // A page with no editors must not leave a save handler waiting on a promise that
  // never settles — the button would stay disabled with no explanation.
  const settled = await page.evaluate(async () => {
    window.OLCS = window.OLCS || {};
    return window.OLCS.editorjsFlush().then(() => "settled");
  });

  expect(settled).toBe("settled");
});

test("a form post carries the typed content without any deliberate pause", async ({ page }) => {
  await typeIntoEditor(page, "editor-variant", " Added by the admin.");

  // Clicking straight after the last keystroke is what previously posted the old
  // content. Nothing here waits, pauses or flushes explicitly — the click is the
  // next thing that happens.
  await page.locator("#host-submit").click();

  expect(await page.evaluate(() => window.__submittedValue)).toContain("Added by the admin.");
});

test("the hidden input tracks typing without waiting for EditorJS's debounce", async ({ page }) => {
  await typeIntoEditor(page, "editor-section", " Tracked.");

  // One frame, far inside EditorJS's own change debounce. This is what makes a plain
  // form post safe: by the time any click lands, the input already agrees with the
  // screen. Code that can await should still use OLCS.editorjsFlush().
  await page.evaluate(() => new Promise((resolve) => requestAnimationFrame(() => setTimeout(resolve, 50))));

  expect(await hiddenInputText(page, "sectionContent")).toContain("Tracked.");
});

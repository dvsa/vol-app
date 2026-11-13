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

test("flushing without an edit leaves the hidden input untouched", async ({ page }) => {
  // EditorJS stamps a fresh `time` on every save(), so the serialised output differs on
  // each call even when nothing was typed. Writing that back marks clean content dirty:
  // letter-edit.js watches this input to decide whether there are unsaved edits, so a
  // spurious write raises "Unsaved changes" over content the caseworker just saved.
  const writes = await page.evaluate(async () => {
    const input = document.querySelector("input[name='sectionContent']");
    await window.OLCS.editorjsFlush();

    let count = 0;
    const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value");
    Object.defineProperty(input, "value", {
      set(v) {
        count++;
        descriptor.set.call(this, v);
      },
      get() {
        return descriptor.get.call(this);
      },
    });

    await window.OLCS.editorjsFlush();
    await window.OLCS.editorjsFlush();
    return count;
  });

  expect(writes).toBe(0);
});

/**
 * Make the editor holding the given wording impossible to read, as a block tool that throws
 * while saving does. EditorJS logs that itself and hands back nothing.
 */
async function breakEditorHolding(page, wording) {
  await page.evaluate((text) => {
    const save = window.Paragraph.prototype.save;
    window.Paragraph.prototype.save = function (content) {
      if (content.innerHTML.includes(text)) {
        throw new Error("cannot be read");
      }
      return save.call(this, content);
    };
  }, wording);
}

test("flushEditors names an editor it could not read and leaves its input alone", async ({ page }) => {
  await page.evaluate(() => window.OLCS.editorjsFlush());
  const before = await page.evaluate(() => document.querySelector("input[name='variantContent']").value);
  await breakEditorHolding(page, "Variant wording");
  await typeIntoEditor(page, "editor-section", " Still readable.");

  // A caller that saves on the strength of a flush has to know which inputs to distrust.
  const unread = await page.evaluate(() => window.OLCS.editorjsFlush());

  expect(unread).toEqual(["variantContent"]);
  expect(await page.evaluate(() => document.querySelector("input[name='variantContent']").value)).toBe(before);
  expect(await hiddenInputText(page, "sectionContent")).toContain("Still readable.");
});

test("typing into an editor that cannot be read keeps the last content that could", async ({ page }) => {
  await page.evaluate(() => window.OLCS.editorjsFlush());
  await breakEditorHolding(page, "Variant wording");

  await typeIntoEditor(page, "editor-variant", " More.");
  // long enough for EditorJS's own debounced change to have come through as well
  await page.waitForTimeout(1000);

  // not the word "undefined", which is what writing an empty result puts in the input
  expect(await hiddenInputText(page, "variantContent")).toBe("Variant wording.");
});

test("an editor that cannot be read as it starts up keeps the content it was given", async ({ page }) => {
  await breakEditorHolding(page, "Late wording");
  await page.evaluate(() => {
    const holder = document.createElement("div");
    holder.innerHTML =
      '<div class="editorjs-container" data-element-name="lateContent">' +
      '<div id="editor-late" class="editorjs-editor"></div>' +
      '<input type="hidden" name="lateContent" value=\'{"blocks":[{"type":"paragraph","data":{"text":"Late wording."}}]}\' />' +
      "</div>";
    document.body.appendChild(holder);
    window.OLCS.eventEmitter.trigger("render");
  });
  await expect(page.locator("#editor-late .ce-paragraph")).toHaveAttribute("contenteditable", "true");
  // the editor writes what it opened on once it's ready
  await page.waitForTimeout(500);

  expect(await hiddenInputText(page, "lateContent")).toBe("Late wording.");
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

test("closing a modal leaves the page's own editors working", async ({ page }) => {
  // Any OLCS modal closing emits hide:modal. The letter edit page opens modals over its
  // editors, and closing one must not take those editors (or unsaved edits) with it.
  await typeIntoEditor(page, "editor-section", " Not saved yet.");
  await page.evaluate(() => window.OLCS.eventEmitter.trigger("hide:modal"));

  await expect(page.locator("#editor-section .ce-paragraph")).toContainText("Not saved yet.");
  await typeIntoEditor(page, "editor-section", " Still editable.");
  await page.evaluate(() => window.OLCS.editorjsFlush());

  expect(await hiddenInputText(page, "sectionContent")).toContain("Still editable.");
});

test("closing a modal cleans up the editors that were inside it", async ({ page }) => {
  await page.evaluate(() => {
    const modal = document.createElement("div");
    modal.id = "modal";
    modal.innerHTML =
      '<div class="editorjs-container" data-element-name="modalContent">' +
      '<div id="editor-modal" class="editorjs-editor"></div>' +
      '<input type="hidden" name="modalContent" value=\'{"blocks":[{"type":"paragraph","data":{"text":"In the modal."}}]}\' />' +
      "</div>";
    document.body.appendChild(modal);
    window.OLCS.eventEmitter.trigger("render");
  });
  await expect(page.locator("#editor-modal .ce-paragraph")).toHaveAttribute("contenteditable", "true");

  // The modal removes its markup before announcing hide:modal.
  const flushed = await page.evaluate(async () => {
    const modalInput = document.querySelector("input[name='modalContent']");
    document.getElementById("modal").remove();
    window.OLCS.eventEmitter.trigger("hide:modal");
    modalInput.value = "untouched";
    await window.OLCS.editorjsFlush();
    return modalInput.value;
  });

  // The removed editor is no longer flushed, and the page editors still are.
  expect(flushed).toBe("untouched");
  await typeIntoEditor(page, "editor-variant", " After the modal.");
  await page.evaluate(() => window.OLCS.editorjsFlush());
  expect(await hiddenInputText(page, "variantContent")).toContain("After the modal.");
});

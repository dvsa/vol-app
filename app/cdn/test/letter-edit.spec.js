// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const url = require("node:url");

/**
 * "Save all changes" on the internal app's Edit letter sections page.
 *
 * Each editor on the page has its own Save changes button. Save all changes presses the ones
 * that have something to save, so a caseworker who has edited several sections doesn't have to
 * find each button. The fixture runs the real page script against the page's markup, with a
 * stand-in for the server.
 */

const FIXTURE = url.pathToFileURL(path.join(__dirname, "fixtures", "letter-edit-harness.html")).href;

const savedUrls = (page) => page.evaluate(() => window.requests.map((request) => request.url));

test.beforeEach(async ({ page }) => {
  await page.goto(FIXTURE);
});

test("saves the editors with unsaved changes, in page order, and leaves the rest alone", async ({ page }) => {
  await page.evaluate(() => {
    window.editorWrites("todo_100", "Upload six months of statements.");
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await expect(page.locator("#save-all-indicator")).toHaveText("All changes saved");
  // Saving an editor marks it as edited, which clears "Input required" on it. So the issue and
  // the appendix, which nobody touched, must not be sent.
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content", "/letter/save-todo-content"]);
  await expect(page.locator(".section-editor-group .save-indicator")).toBeVisible();
  await expect(page.locator(".todo-editor-group .save-indicator")).toBeVisible();
  await expect(page.locator(".issue-editor-group .save-indicator")).toBeHidden();
  await expect(page.locator("#save-all-btn")).toBeEnabled();
  await expect(page.locator("#save-all-btn")).toHaveText("Save all changes");
});

test("saves what was typed just before the click", async ({ page }) => {
  // the editor only writes typing to its hidden input after a pause, so straight after a
  // keystroke the page doesn't yet know the issue has changed
  await page.evaluate(() => {
    window.pendingTyping["issue-10"] = "Your bank statements are in the wrong name.";
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  const requests = await page.evaluate(() => window.requests);
  expect(requests.map((request) => request.url)).toEqual(["/letter/save-issue-content"]);
  expect(JSON.parse(requests[0].body.editedContent).blocks[0].data.text).toBe(
    "Your bank statements are in the wrong name.",
  );
});

test("with nothing to save, sends nothing and says so", async ({ page }) => {
  await page.locator("#save-all-btn").click();

  // not "saved": if an edit had somehow gone unnoticed, that would hide it
  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await expect(page.locator("#save-all-indicator")).toHaveText("No changes to save");
  expect(await savedUrls(page)).toEqual([]);
});

test("names each section that could not be saved, and still saves the others", async ({ page }) => {
  await page.evaluate(() => {
    window.failures["/letter/save-section-content"] = "This letter was changed by someone else";
    window.failures["/letter/save-appendix-content"] = "This letter can no longer be changed";
    window.editorWrites("section_1", "Dear Mr Smith");
    window.editorWrites("issue-10", "Your bank statements are in the wrong name.");
    window.editorWrites("appendix-7", "Evidence we no longer accept.");
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#edit-error-list li")).toHaveText([
    "Introductory wording: This letter was changed by someone else",
    "Financial evidence: This letter can no longer be changed",
  ]);
  await expect(page.locator(".issue-editor-group .save-indicator")).toBeVisible();
  await expect(page.locator(".section-editor-group .save-indicator")).toBeHidden();
  await expect(page.locator("#save-all-btn")).toBeEnabled();
  // two sections are still unsaved, so it must not claim otherwise
  await expect(page.locator("#save-all-indicator")).toBeHidden();
});

test("an earlier failure is cleared when everything then saves", async ({ page }) => {
  await page.evaluate(() => {
    window.failures["/letter/save-section-content"] = "This letter was changed by someone else";
    window.editorWrites("section_1", "Dear Mr Smith");
  });
  await page.locator("#save-all-btn").click();
  await expect(page.locator("#edit-error-list li")).toHaveCount(1);

  await page.evaluate(() => {
    window.failures = {};
  });
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await expect(page.locator("#edit-error-summary")).toBeHidden();
});

test("the saved message goes once something is changed again", async ({ page }) => {
  await page.locator("#save-all-btn").click();
  await expect(page.locator("#save-all-indicator")).toBeVisible();

  await page.evaluate(() => window.editorWrites("issue-10", "Your bank statements are in the wrong name."));

  await expect(page.locator("#save-all-indicator")).toBeHidden();
});

test("a single Save changes still shows only its own failure", async ({ page }) => {
  await page.evaluate(() => {
    window.failures["/letter/save-section-content"] = "This letter was changed by someone else";
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator(".save-section-btn").click();
  await expect(page.locator("#edit-error-list li")).toHaveText(["This letter was changed by someone else"]);

  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toHaveText("Save changes");
  await expect(page.locator("#edit-error-list li")).toHaveCount(1);
});

test("an editor that is already being saved is not sent a second time", async ({ page }) => {
  await page.evaluate(() => {
    // slow enough that Save all is pressed while the first save is still on its way
    window.serverDelay = 1000;
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toBeDisabled();
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  // the save that was on its way counts: something was saved
  await expect(page.locator("#save-all-indicator")).toHaveText("All changes saved");
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content"]);
});

test("an editor typed into while its own save is on its way is saved again with the newer text", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 1000;
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toBeDisabled();
  // the first save has already gone with the text above
  await page.evaluate(() => window.editorWrites("section_1", "Dear Mr Smith and Mrs Smith"));
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await expect(page.locator("#save-all-indicator")).toHaveText("All changes saved");
  const requests = await page.evaluate(() => window.requests);
  expect(requests.map((request) => JSON.parse(request.body.editedContent).blocks[0].data.text)).toEqual([
    "Dear Mr Smith",
    "Dear Mr Smith and Mrs Smith",
  ]);
  // the second goes with the version the first save came back with, or it would be refused
  expect(requests.map((request) => request.body.version)).toEqual([1, 2]);
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual([]);
});

const UNREAD =
  "The latest changes in this section could not be read, so they have not been saved. " +
  "Try again, or copy your text and reload the page.";

test("an editor that could not be read is named and not sent, and the others are still saved", async ({ page }) => {
  await page.evaluate(() => {
    window.unreadable = ["issue-10"];
    window.pendingTyping["issue-10"] = "Your bank statements are in the wrong name.";
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#edit-error-list li")).toHaveText(["Bank statements: " + UNREAD]);
  await expect(page.locator(".section-editor-group .save-indicator")).toBeVisible();
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content"]);
  await expect(page.locator("#save-all-btn")).toBeEnabled();
  // what was typed there is still only on screen, so nothing may say it's saved
  await expect(page.locator("#save-all-indicator")).toBeHidden();
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Bank statements"]);
});

test("an editor that could not be read is not passed over as having nothing to save", async ({ page }) => {
  await page.evaluate(() => {
    window.unreadable = ["issue-10"];
    window.pendingTyping["issue-10"] = "Your bank statements are in the wrong name.";
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#edit-error-list li")).toHaveText(["Bank statements: " + UNREAD]);
  await expect(page.locator("#save-all-btn")).toBeEnabled();
  await expect(page.locator("#save-all-indicator")).toBeHidden();
  expect(await savedUrls(page)).toEqual([]);

  // once it can be read again, saving picks up what was typed
  await page.evaluate(() => {
    window.unreadable = [];
  });
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await expect(page.locator("#save-all-indicator")).toHaveText("All changes saved");
  await expect(page.locator("#edit-error-summary")).toBeHidden();
  expect(await savedUrls(page)).toEqual(["/letter/save-issue-content"]);
});

test("an editor the component could not read counts as unsaved before anything is saved", async ({ page }) => {
  await page.locator("#save-all-btn").click();
  await expect(page.locator("#save-all-indicator")).toBeVisible();

  // What the component does when a change can't be read: the input is left as it was, so no
  // write arrives to show the editor now holds something else.
  await page.evaluate(() => {
    document.querySelector("input[name='issue-10']").dispatchEvent(new CustomEvent("editorjs:unread"));
  });

  // so going back to the preview, or changing the issues, warns about it
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Bank statements"]);
  await expect(page.locator("#save-all-indicator")).toBeHidden();
});

test("an editor that turns unreadable while its save is on its way is not shown as saved", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 500;
    window.editorWrites("issue-10", "Your bank statements are in the wrong name.");
  });

  await page.locator(".save-issue-btn").click();
  await expect(page.locator(".save-issue-btn")).toBeDisabled();
  // more is typed and can't be read, so the input still holds exactly what was sent
  await page.evaluate(() => {
    document.querySelector("input[name='issue-10']").dispatchEvent(new CustomEvent("editorjs:unread"));
  });
  await expect(page.locator(".save-issue-btn")).toBeEnabled();

  expect(await savedUrls(page)).toEqual(["/letter/save-issue-content"]);
  await expect(page.locator(".issue-editor-group .save-indicator")).toBeHidden();
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Bank statements"]);
});

test("each save brings its own editor up to date, not every editor on the page", async ({ page }) => {
  await page.evaluate(() => {
    window.editorWrites("section_1", "Dear Mr Smith");
    window.editorWrites("todo_100", "Upload six months of statements.");
    window.pendingTyping["appendix-7"] = "Evidence we no longer accept.";
    window.flushes = [];
  });

  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-indicator")).toBeVisible();
  // once round all of them to find what's unsaved, then one each
  expect(await page.evaluate(() => window.flushes)).toEqual(["all", "section_1", "todo_100", "appendix-7"]);
  expect(await savedUrls(page)).toEqual([
    "/letter/save-section-content",
    "/letter/save-todo-content",
    "/letter/save-appendix-content",
  ]);
});

test("an editor with its own save already queued is left to that save", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 500;
    window.unreadable = ["issue-10"];
    window.editorWrites("section_1", "Dear Mr Smith");
    window.editorWrites("todo_100", "Upload six months of statements.");
  });

  // the issue's save is waiting its turn behind the section's when Save all is pressed
  await page.locator(".save-section-btn").click();
  await page.locator(".save-issue-btn").click();
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-btn")).toBeEnabled();
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content", "/letter/save-todo-content"]);
  // said once, by the issue's own save
  await expect(page.locator("#edit-error-list li")).toHaveText(["Bank statements: " + UNREAD]);
  await expect(page.locator("#save-all-indicator")).toBeHidden();
});

test("Save all's second look at an editor does not carry over to its next Save changes", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 500;
    window.unreadable = ["issue-10"];
    window.editorWrites("section_1", "Dear Mr Smith");
  });
  // the issue's save is waiting when Save all is pressed, and then can't be read
  await page.locator(".save-section-btn").click();
  await page.locator(".save-issue-btn").click();
  await page.locator("#save-all-btn").click();
  await expect(page.locator("#save-all-btn")).toBeEnabled();

  await page.evaluate(() => {
    window.unreadable = [];
    window.editorWrites("issue-10", "Your bank statements are too old.");
  });
  await page.locator(".save-issue-btn").click();
  await expect(page.locator(".save-issue-btn")).toBeDisabled();
  await page.evaluate(() => window.editorWrites("issue-10", "Your bank statements are in the wrong name."));
  await expect(page.locator(".save-issue-btn")).toBeEnabled();

  // on its own, Save changes sends once: what's typed while it's away waits for the next press
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content", "/letter/save-issue-content"]);
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Bank statements"]);
});

test("a save refused while Save all is running is reported, and not sent again", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 1000;
    window.failures["/letter/save-section-content"] = "This letter was changed by someone else";
    window.editorWrites("section_1", "Dear Mr Smith");
  });

  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toBeDisabled();
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-btn")).toBeEnabled();
  await expect(page.locator("#edit-error-list li")).toHaveText([
    "Introductory wording: This letter was changed by someone else",
  ]);
  await expect(page.locator("#save-all-indicator")).toBeHidden();
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content"]);
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Introductory wording"]);
});

test("nothing says saved when a save of an untouched editor is refused meanwhile", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 1000;
    window.failures["/letter/save-section-content"] = "This letter can no longer be changed";
  });

  // nothing was typed, so the section never counts as unsaved: only the error can say it failed
  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toBeDisabled();
  await page.locator("#save-all-btn").click();

  await expect(page.locator("#save-all-btn")).toBeEnabled();
  await expect(page.locator("#edit-error-list li")).toHaveText([
    "Introductory wording: This letter can no longer be changed",
  ]);
  await expect(page.locator("#save-all-indicator")).toBeHidden();
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content"]);
});

test("saves pressed by Save all go before a to-do added while it waits", async ({ page }) => {
  await page.evaluate(() => {
    window.serverDelay = 500;
    window.editorWrites("section_1", "Dear Mr Smith");
    window.editorWrites("issue-10", "Your bank statements are in the wrong name.");
    window.editorWrites("appendix-7", "Evidence we no longer accept.");
    // what "Add a to-do" does: its request waits its turn behind the saves
    $("<button type='button' id='add-todo'>Add a to-do</button>")
      .appendTo(document.body)
      .on("click", function () {
        $(".save-todo-btn").trigger("click");
      });
  });

  await page.locator(".save-section-btn").click();
  await expect(page.locator(".save-section-btn")).toBeDisabled();
  await page.locator("#save-all-btn").click();
  await page.locator("#add-todo").click();

  await expect(page.locator("#save-all-btn")).toBeEnabled();
  await expect(page.locator(".save-todo-btn")).toBeEnabled();
  expect(await savedUrls(page)).toEqual([
    "/letter/save-section-content",
    "/letter/save-issue-content",
    "/letter/save-appendix-content",
    "/letter/save-todo-content",
  ]);
});

test("Save changes on an editor that could not be read sends nothing and says so", async ({ page }) => {
  await page.locator("#save-all-btn").click();
  await expect(page.locator("#save-all-indicator")).toBeVisible();
  await page.evaluate(() => {
    window.unreadable = ["issue-10"];
    window.pendingTyping["issue-10"] = "Your bank statements are in the wrong name.";
  });

  await page.locator(".save-issue-btn").click();

  await expect(page.locator("#edit-error-list li")).toHaveText([UNREAD]);
  // the message from the earlier Save all no longer holds
  await expect(page.locator("#save-all-indicator")).toBeHidden();
  await expect(page.locator(".save-issue-btn")).toBeEnabled();
  await expect(page.locator(".save-issue-btn")).toHaveText("Save changes");
  await expect(page.locator(".issue-editor-group .save-indicator")).toBeHidden();
  expect(await savedUrls(page)).toEqual([]);
  expect(await page.evaluate(() => window.OLCS.letterEdit.unsavedSectionNames())).toEqual(["Bank statements"]);
});

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
  expect(await savedUrls(page)).toEqual(["/letter/save-section-content"]);
});

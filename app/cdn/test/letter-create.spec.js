// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const url = require("node:url");

/**
 * "Start from a letter already sent" on the internal app's New letter dialog.
 *
 * A final request usually covers what the first request did. Picking the earlier letter ticks
 * the issues it covered, so the caseworker only has to change what's different. The fixture
 * runs the real dialog script against the form the dialog shows.
 */

const FIXTURE = url.pathToFileURL(path.join(__dirname, "fixtures", "letter-create-harness.html")).href;

const ticked = (page) =>
  page.evaluate(() => [...document.querySelectorAll("input[name='letterIssues[]']:checked")].map((box) => box.value));

test.beforeEach(async ({ page }) => {
  await page.goto(FIXTURE);
  await expect(page.locator(".modal__content #letter-create-form")).toBeVisible();
});

test("picking a letter ticks the issues it covered and opens their groups", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");

  expect(await ticked(page)).toEqual(["1", "15"]);
  await expect(page.locator("#adverts .letter-section__content")).toBeVisible();
  await expect(page.locator("#adverts .letter-section__show-hide")).toHaveText("Hide");
  await expect(page.locator("#finances .letter-section__content")).toBeVisible();
  // there is now something to create a letter from
  await expect(page.locator("#create-letter-btn")).toBeEnabled();
  await expect(page.locator("#previous-letter-note")).toBeHidden();
});

test("picking a different letter replaces the ticks and closes the groups it has nothing in", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#previous-letter").selectOption("388");

  expect(await ticked(page)).toEqual(["5"]);
  await expect(page.locator("#adverts .letter-section__content")).toBeVisible();
  await expect(page.locator("#finances .letter-section__content")).toBeHidden();
  await expect(page.locator("#finances .letter-section__show-hide")).toHaveText("Show");
});

test("the ticks can be changed afterwards, and going back to no letter leaves them alone", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#letter-issue-15").uncheck();
  await page.locator("#letter-issue-5").check();

  await page.locator("#previous-letter").selectOption("");

  expect(await ticked(page)).toEqual(["1", "5"]);
});

test("says when issues on the earlier letter are no longer offered", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("385");

  expect(await ticked(page)).toEqual(["16"]);
  await expect(page.locator("#previous-letter-note")).toHaveText(
    "2 issues on that letter are no longer offered, so they are not ticked.",
  );

  await page.locator("#previous-letter").selectOption("380");
  await expect(page.locator("#previous-letter-note")).toHaveText(
    "1 issue on that letter is no longer offered, so it is not ticked.",
  );

  // a letter with nothing missing clears the note
  await page.locator("#previous-letter").selectOption("388");
  await expect(page.locator("#previous-letter-note")).toBeHidden();
});

test("the groups still open and close by hand", async ({ page }) => {
  await page.locator("#adverts .letter-section__header").click();
  await expect(page.locator("#adverts .letter-section__content")).toBeVisible();
  await expect(page.locator("#adverts .letter-section__show-hide")).toHaveText("Hide");

  await page.locator("#adverts .letter-section__header").click();
  await expect(page.locator("#adverts .letter-section__content")).toBeHidden();
  await expect(page.locator("#adverts .letter-section__show-hide")).toHaveText("Show");
});

test("the show all link follows what is open, however the groups were opened", async ({ page }) => {
  const showAll = page.locator("#toggle-all-sections");

  // this letter has an issue in both groups, so picking it opens everything
  await page.locator("#previous-letter").selectOption("392");
  await expect(showAll).toHaveText("Hide All Sections");

  await showAll.click();
  await expect(page.locator("#adverts .letter-section__content")).toBeHidden();
  await expect(showAll).toHaveText("Show All Sections");

  await page.locator("#adverts .letter-section__header").click();
  await expect(showAll).toHaveText("Show All Sections");
  await page.locator("#finances .letter-section__header").click();
  await expect(showAll).toHaveText("Hide All Sections");
});

test("ticks made by hand are only replaced if the caseworker agrees", async ({ page }) => {
  const asked = [];
  let agree = false;
  page.on("dialog", (dialog) => {
    asked.push(dialog.message());
    return agree ? dialog.accept() : dialog.dismiss();
  });

  await page.locator("#adverts .letter-section__header").click();
  await page.locator("#letter-issue-5").check();

  await page.locator("#previous-letter").selectOption("392");
  expect(asked).toHaveLength(1);
  expect(await ticked(page)).toEqual(["5"]);
  // the picker goes back to what it was, so it doesn't claim a letter that wasn't used
  await expect(page.locator("#previous-letter")).toHaveValue("");

  agree = true;
  await page.locator("#previous-letter").selectOption("392");
  expect(asked).toHaveLength(2);
  expect(await ticked(page)).toEqual(["1", "15"]);
});

test("ticks the picker made itself are replaced without asking", async ({ page }) => {
  const asked = [];
  page.on("dialog", (dialog) => {
    asked.push(dialog.message());
    return dialog.dismiss();
  });

  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#previous-letter").selectOption("388");
  expect(await ticked(page)).toEqual(["5"]);
  expect(asked).toEqual([]);

  // but once one of them has been changed by hand, they are the caseworker's
  await page.locator("#letter-issue-1").check();
  await page.locator("#previous-letter").selectOption("392");
  expect(asked).toHaveLength(1);
  expect(await ticked(page)).toEqual(["1", "5"]);
  await expect(page.locator("#previous-letter")).toHaveValue("388");
});

// What the form sent when Create letter was pressed, by field
const posted = async (page) => {
  await page.locator("#create-letter-btn").click();
  await expect.poll(() => page.evaluate(() => window.posted.length), "the form was posted").toBe(1);
  return new URLSearchParams(await page.evaluate(() => window.posted[0]));
};

test("the letter picked is posted with the form, so the wording changed on it can be copied", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");

  const form = await posted(page);
  expect(form.get("startFromLetter")).toBe("392");
  expect(form.getAll("letterIssues[]")).toEqual(["1", "15"]);
});

test("going back to no letter posts no letter, so nothing is copied", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#previous-letter").selectOption("");

  const form = await posted(page);
  expect(form.get("startFromLetter")).toBe("");
  expect(form.getAll("letterIssues[]")).toEqual(["1", "15"]);
});

test("a letter the caseworker decided against is not posted", async ({ page }) => {
  page.on("dialog", (dialog) => dialog.dismiss());

  await page.locator("#adverts .letter-section__header").click();
  await page.locator("#letter-issue-5").check();
  await page.locator("#previous-letter").selectOption("392");

  const form = await posted(page);
  expect(form.get("startFromLetter")).toBe("");
  expect(form.getAll("letterIssues[]")).toEqual(["5"]);
});

test("names the issues that had wording copied, for the caseworker to check", async ({ page }) => {
  await page.evaluate(() => {
    window.serverAnswer = {
      success: true,
      letterInstanceId: 401,
      // a heading is the caseworker's own words on an Other issue, so it is shown as text
      wordingCopiedTo: ["No financial evidence", "Lease <b>missing</b>"],
    };
  });
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#create-letter-btn").click();

  const note = page.locator(".modal__content .letter-wording-copied");
  await expect(note).toBeVisible();
  await expect(note.locator("li")).toHaveText(["No financial evidence", "Lease <b>missing</b>"]);
  await expect(note).toContainText("Check");
  await expect(note.locator("b")).toHaveCount(0);
  // once: the step shown replaces the form and the copy of the step that sat beside it
  await expect(page.locator(".letter-wording-copied")).toHaveCount(1);
});

test("the note is in view when the dialog moves on, however far down Create letter was", async ({ page }) => {
  // a short screen: neither the form nor the step after it fits, and Create letter is at the
  // form's foot
  await page.setViewportSize({ width: 1366, height: 420 });
  await page.evaluate(() => {
    window.serverAnswer = {
      success: true,
      letterInstanceId: 401,
      warnings: ['Required section "Signature" could not be included'],
      wordingCopiedTo: ["No financial evidence", "Funds only shown as a balance", "Missing lease"],
    };
  });
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#create-letter-btn").scrollIntoViewIfNeeded();
  expect(await page.evaluate(() => document.querySelector(".modal__wrapper").scrollTop)).toBeGreaterThan(0);
  await page.locator("#create-letter-btn").click();
  // the step has replaced the form (a hidden copy of the step was beside the form all along,
  // so waiting for something in the step would not wait)
  await expect(page.locator("#letter-create-form")).toHaveCount(0);

  // the step is taller than the dialog, so where the dialog is scrolled to decides what shows
  const dialog = await page.evaluate(() => {
    const wrapper = document.querySelector(".modal__wrapper");
    return { fits: wrapper.scrollHeight <= wrapper.clientHeight, scrolledTo: wrapper.scrollTop };
  });
  expect(dialog.fits, "the step should not fit, or this proves nothing").toBe(false);
  expect(dialog.scrolledTo, "the step is shown from its top").toBe(0);

  // so the warning about sections, which goes first, and the note under it start in view
  const warning = page.locator(".modal__content .govuk-warning-text");
  const note = page.locator(".modal__content .letter-wording-copied");
  await expect(warning).toBeInViewport({ ratio: 1 });
  await expect(note).toBeInViewport();
  const [warningBox, noteBox] = [await warning.boundingBox(), await note.boundingBox()];
  expect(warningBox.y).toBeLessThan(noteBox.y);
});

test("says nothing about copied wording when none was copied", async ({ page }) => {
  await page.locator("#previous-letter").selectOption("392");
  await page.locator("#create-letter-btn").click();

  await expect(page.locator("#letter-create-form")).toHaveCount(0);
  await expect(page.locator(".modal__content h3")).toHaveText("Check and edit your letter");
  await expect(page.locator(".letter-wording-copied")).toHaveCount(0);
});

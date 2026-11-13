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

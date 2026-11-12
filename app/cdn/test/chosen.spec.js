// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const url = require("node:url");

const FIXTURE = url.pathToFileURL(path.join(__dirname, "fixtures", "chosen-harness.html")).href;

test("typing in the search box filters the options", async ({ page }) => {
  const errors = [];
  page.on("pageerror", (e) => errors.push(e.message));
  await page.goto(FIXTURE);

  await page.locator("#colour_chosen .chosen-single").click();
  await page.locator("#colour_chosen .chosen-search input").pressSequentially("gre");

  await expect(page.locator("#colour_chosen .active-result")).toHaveText(["Green"]);
  expect(errors).toEqual([]);
});

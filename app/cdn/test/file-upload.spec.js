// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const url = require("node:url");

const FIXTURE = url.pathToFileURL(path.join(__dirname, "fixtures", "file-upload-harness.html")).href;

const CORS = { "access-control-allow-origin": "*", "access-control-allow-headers": "*" };

const uploaderWith = (fileName) => `
  <div><fieldset class="file-uploader" data-group="file">
    <ul class="js-upload-list"><li class="file"><a href="/file/1">${fileName}</a></li></ul>
  </fieldset></div>`;

async function holdUploads(page) {
  const waiting = [];
  await page.route("http://localhost:9/upload", (route) => {
    if (route.request().method() === "OPTIONS") {
      return route.fulfill({ status: 204, headers: CORS });
    }
    waiting.push(route);
  });

  return {
    count: () => waiting.length,
    answerNext: (body) => waiting.shift().fulfill({ status: 200, contentType: "text/html", headers: CORS, body }),
  };
}

const choose = (page, ...names) =>
  page
    .locator(".attach-action__input")
    .setInputFiles(names.map((name) => ({ name, mimeType: "application/pdf", buffer: Buffer.from("%PDF-1.4") })));

test.beforeEach(async ({ page }) => {
  await page.goto(FIXTURE);
});

test("the form's buttons are held back while a file uploads and released when it finishes", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "form.pdf");
  await expect.poll(uploads.count).toBe(1);

  await expect(page.locator("#send")).toBeDisabled();

  await uploads.answerNext(uploaderWith("form.pdf"));

  await expect(page.locator(".js-upload-list a")).toHaveText("form.pdf");
  await expect(page.locator("#send")).toBeEnabled();
});

test("with two files they stay held back until the last one finishes", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "one.pdf", "two.pdf");
  await expect.poll(uploads.count, { timeout: 5_000 }).toBe(2);

  await uploads.answerNext(uploaderWith("one.pdf"));
  await expect(page.locator("#send")).toBeDisabled();

  await uploads.answerNext(uploaderWith("two.pdf"));
  await expect(page.locator("#send")).toBeEnabled();
});

test("when the answer has no upload list the file is not left looking attached", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "form.pdf");
  await expect.poll(uploads.count).toBe(1);
  await expect(page.locator("#send")).toBeDisabled();
  await expect(page.locator(".js-upload-list li")).toHaveCount(1);

  await uploads.answerNext("<html><body><h1>This page isn't working at the moment</h1></body></html>");

  await expect(page.locator("#send")).toBeEnabled();
  await expect(page.locator(".js-upload-list li")).toHaveCount(0);
});

test("another list of files on the page is not mistaken for the answer", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "form.pdf");
  await expect.poll(uploads.count).toBe(1);

  await uploads.answerNext(
    '<div><div class="file-uploader"><ul><li class="file"><a href="/file/9">sent earlier.pdf</a></li></ul></div></div>',
  );

  await expect(page.locator("#send")).toBeEnabled();
  await expect(page.locator("#message")).not.toContainText("sent earlier.pdf");
  await expect(page.locator(".js-upload-list li")).toHaveCount(0);
});

test("choosing another file while one is uploading keeps them held back until both finish", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "one.pdf");
  await expect.poll(uploads.count).toBe(1);
  await choose(page, "two.pdf");
  await expect.poll(uploads.count).toBe(2);

  await uploads.answerNext(uploaderWith("one.pdf"));
  await expect(page.locator("#send")).toBeDisabled();

  await uploads.answerNext(uploaderWith("two.pdf"));
  await expect(page.locator("#send")).toBeEnabled();
  await expect(page.locator(".js-upload-list a")).toHaveText("two.pdf");
});

test("a button that was already unavailable is left that way", async ({ page }) => {
  const uploads = await holdUploads(page);

  await choose(page, "form.pdf");
  await expect.poll(uploads.count).toBe(1);
  await uploads.answerNext(uploaderWith("form.pdf"));

  await expect(page.locator("#send")).toBeEnabled();
  await expect(page.locator("#locked")).toBeDisabled();
});

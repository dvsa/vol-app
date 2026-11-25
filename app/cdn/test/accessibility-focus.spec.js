// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const sass = require("sass");

const assets = path.join(__dirname, "../assets");

for (const app of ["internal", "selfserve"]) {
  test(`${app} keeps the file upload focus indicator with native input focus`, async ({ page }) => {
    const css = sass.compile(path.join(assets, `_styles/themes/${app}.scss`), {
      logger: sass.Logger.silent,
    }).css;
    await page.setContent(`
      <html class="js-enabled govuk-frontend-supported">
        <body>
          <label class="attach-action__label">Attach file(s)
            <input class="attach-action__input js-visually-hidden" type="file">
          </label>
          <button>Continue</button>
          <div class="surrenderChecks">
            <div class="govuk-checkboxes" data-module="govuk-checkboxes">
              <div class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input surrenderChecks__checkbox" type="checkbox" id="surrender">
                <label class="govuk-label govuk-checkboxes__label" for="surrender">Check</label>
              </div>
              <div class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input surrenderChecks__checkbox" type="checkbox" id="surrender-disabled" checked disabled>
                <label class="govuk-label govuk-checkboxes__label" for="surrender-disabled">Checked</label>
              </div>
            </div>
          </div>
        </body>
      </html>
    `);
    await page.addStyleTag({ content: css });

    await page.keyboard.press("Tab");
    const input = page.locator('input[type="file"]');
    const label = page.locator(".attach-action__label");
    await expect(input).toBeFocused();
    await expect(label).toHaveCSS("background-color", "rgb(255, 221, 0)");
    await input.setInputFiles({
      name: "test.txt",
      mimeType: "text/plain",
      buffer: Buffer.from("test"),
    });
    await expect.poll(() => input.evaluate((element) => element.files[0].name)).toBe("test.txt");
    await page.keyboard.press("Tab");
    await expect(page.getByRole("button", { name: "Continue" })).toBeFocused();
    await expect(label).not.toHaveCSS("background-color", "rgb(255, 221, 0)");
    if (app === "internal") {
      const checkbox = page.locator("#surrender");
      await expect(checkbox).toBeInViewport();
      await checkbox.check();
      await expect(checkbox).toBeChecked();
      await expect(checkbox).toBeInViewport();
      await expect(page.locator("#surrender-disabled")).toBeInViewport();
    }
  });
}

test("rendering preserves native checkbox, radio and file focus and the skip link", async ({ page }) => {
  await page.setContent(`
    <a href="#main" id="skipToContent">Skip to content</a>
    <main id="main">
      <label id="checkbox-label"><input type="checkbox" id="checkbox">Checkbox</label>
      <label id="radio-label"><input type="radio" id="radio">Radio</label>
      <label id="file-label"><input type="file" id="file">File</label>
      <label class="disabled"><input type="checkbox" disabled>Disabled</label>
    </main>
  `);
  await page.addScriptTag({ path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js") });
  await page.addScriptTag({ path: path.join(assets, "_js/components/eventEmitter.js") });
  await page.addScriptTag({ path: path.join(assets, "_js/components/accessibility.js") });
  await page.evaluate(() => {
    window.OLCS.logger = { debug() {}, verbose() {} };
    window.OLCS.accessibility();
    window.OLCS.eventEmitter.emit("render");
    window.OLCS.eventEmitter.emit("render");
  });

  for (const id of ["checkbox", "radio", "file"]) {
    await expect(page.locator(`#${id}`)).not.toHaveAttribute("tabindex", "-1");
    await expect(page.locator(`#${id}-label`)).not.toHaveAttribute("tabindex", /.+/);
  }
  await page.getByText("Skip to content").click();
  await expect(page.locator("#main")).toBeFocused();
  await page.keyboard.press("Tab");
  await expect(page.locator("#checkbox")).toBeFocused();
  await page.keyboard.press("Space");
  await expect(page.locator("#checkbox")).toBeChecked();
  await page.keyboard.press("Tab");
  await expect(page.locator("#radio")).toBeFocused();
  await page.keyboard.press("Tab");
  await expect(page.locator("#file")).toBeFocused();
  await expect(page.locator("#file-label")).not.toHaveClass(/focused/);
  await page.evaluate(() => {
    document
      .querySelector("#main")
      .insertAdjacentHTML("beforeend", '<label id="new-file-label"><input type="file" id="new-file">New file</label>');
    window.OLCS.eventEmitter.emit("render");
  });
  await page.keyboard.press("Tab");
  await expect(page.locator("#new-file")).toBeFocused();
  await expect(page.locator("#new-file-label")).not.toHaveAttribute("tabindex", /.+/);
});

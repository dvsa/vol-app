// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const fs = require("node:fs");

const targets = [
  ["licence[safetyInsVehicles]", "Vehicle inspection interval"],
  ["licence[safetyInsTrailers]", "Trailer inspection interval"],
  ["licence[safetyInsVaries]", "More frequent inspections"],
  ["licence[tachographIns]", "Tachograph analyser"],
  ["licence[tachographInsName]", "Contractor name"],
];

for (const viewport of [
  { width: 1280, height: 720 },
  { width: 375, height: 667 },
]) {
  for (const app of ["internal", "selfserve"]) {
    test.describe(`${app} failed sign-in summary at ${viewport.width}px`, () => {
      test.use({ viewport });

      test("uses GOV.UK markup and takes focus on page load", async ({ page }) => {
        const source = fs.readFileSync(
          path.join(__dirname, `../../${app}/module/Olcs/view/pages/auth/login.phtml`),
          "utf8",
        );
        let summary;
        if (app === "internal") {
          summary = source
            .split("if (isset($failed) && $failed) {")[1]
            .split("$form->get('password')")[0]
            .replace(/<\?php[\s\S]*?\?>/g, "")
            .replace(/\?>/, "");
        } else {
          const format = source.split("$formErrors .= sprintf(")[1].split("$this->escapeHtml")[0];
          summary = [...format.matchAll(/'([^']*)'/g)]
            .map((match) => match[1])
            .join("")
            .replace(/%s/g, "Sign-in failed");
        }
        await page.setContent(`
          <body class="govuk-frontend-supported">
            <div style="height: 1000px"></div>
            ${summary}
            <input aria-label="Username">
            <div style="height: 1000px"></div>
          </body>
        `);
        await page.addScriptTag({
          path: path.join(
            __dirname,
            "../node_modules/govuk-frontend/dist/govuk/components/error-summary/error-summary.bundle.js",
          ),
        });
        await page.evaluate(() => {
          document.querySelectorAll('[data-module="govuk-error-summary"]').forEach((summary) => {
            new window.GOVUKFrontend.ErrorSummary(summary);
          });
        });

        const errorSummary = page.locator(".govuk-error-summary");
        await expect(errorSummary).toHaveCount(1);
        await expect(errorSummary.locator('[role="alert"]')).toHaveCount(1);
        await expect(errorSummary.locator(".govuk-error-summary__title")).toHaveCount(1);
        await expect(errorSummary.locator(".govuk-error-summary__body p")).toHaveCount(1);
        await expect(errorSummary).toBeFocused();
        await expect(errorSummary).toBeInViewport({ ratio: 1 });
        await expect(page.locator(".validation-summary")).toHaveCount(0);
      });
    });
  }

  test.describe(`safety error summary at ${viewport.width}px`, () => {
    test.use({ viewport });

    test.beforeEach(async ({ page }) => {
      const links = targets.map(([id, label]) => `<li><a href="#${id}">${label}</a></li>`).join("");
      await page.setContent(`
        <body class="govuk-frontend-supported">
          <style>
            body { margin: 0; }
            .govuk-error-summary { margin-bottom: 500px; }
            .field, fieldset { margin: 0 0 30px; padding: 0; border: 0; }
            label { display: block; }
            input[type=text] { height: 30px; }
            footer { height: 1000px; }
          </style>
          <div class="govuk-error-summary" data-module="govuk-error-summary">
            <h2>There is a problem</h2><ul>${links}</ul>
          </div>
          <form>
            <fieldset data-group="licence">
              <div class="field">
                <label for="licence[safetyInsVehicles]">Vehicle inspection interval</label>
                <input type="text" id="licence[safetyInsVehicles]">
              </div>
              <div class="field">
                <label for="licence[safetyInsTrailers]">Trailer inspection interval</label>
                <input type="text" id="licence[safetyInsTrailers]">
              </div>
              <fieldset>
                <legend>More frequent inspections</legend>
                <label><input type="radio" id="licence[safetyInsVaries]">Yes</label>
              </fieldset>
              <fieldset>
                <legend>Tachograph analyser</legend>
                <label><input type="radio" id="licence[tachographIns]">External</label>
              </fieldset>
              <div class="field">
                <label for="licence[tachographInsName]">Contractor name</label>
                <input type="text" id="licence[tachographInsName]">
              </div>
            </fieldset>
          </form>
          <footer></footer>
        </body>
      `);
      await page.addScriptTag({
        path: path.join(
          __dirname,
          "../node_modules/govuk-frontend/dist/govuk/components/error-summary/error-summary.bundle.js",
        ),
      });
      await page.addScriptTag({
        path: path.join(__dirname, "../assets/_js/components/errorSummary.js"),
      });
      await page.addScriptTag({
        path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js"),
      });
      await page.evaluate(() => {
        window.OLCS.logger = { debug() {}, verbose() {} };
      });
      await page.addScriptTag({
        path: path.join(__dirname, "../assets/_js/components/eventEmitter.js"),
      });
      await page.evaluate(() => {
        window.OLCS.errorSummary(window.GOVUKFrontend.ErrorSummary);
        new window.GOVUKFrontend.ErrorSummary(document.querySelector(".govuk-error-summary"));
      });
    });

    for (const [id, label] of targets) {
      test(`keeps ${label.toLowerCase()} and its label visible after following an error`, async ({ page }) => {
        await page.getByRole("link", { name: label, exact: true }).click();
        const input = page.locator(`[id="${id}"]`);
        await expect(input).toBeFocused();
        await expect(input).toBeInViewport({ ratio: 1 });
        const heading = await input.evaluate((element) => {
          const label =
            element.type === "radio"
              ? element.closest("fieldset").querySelector("legend")
              : document.querySelector(`label[for="${element.id}"]`);
          return label?.getBoundingClientRect().top;
        });
        // scrollIntoView can leave a subpixel rounding sliver above the viewport.
        expect(heading).toBeGreaterThanOrEqual(-1);
        expect(heading).toBeLessThan(viewport.height / 2);
      });
    }
  });

  test.describe(`postcode error summary at ${viewport.width}px`, () => {
    test.use({ viewport });

    function body(errors, address = "") {
      return `
            ${
              errors
                ? `<div class="govuk-error-summary" data-module="govuk-error-summary">
              <div role="alert"><h2>There is a problem</h2>
                <ul><li><a href="#postcodeInput">${errors}</a></li></ul>
              </div>
            </div>`
                : ""
            }
            <h1>Addresses</h1>
            <form>
              <fieldset class="js-postcode-search" data-group="address">
                <div class="field" style="margin-top: 1000px">
                  <label for="postcodeInput">Postcode</label>
                  <input type="text" id="postcodeInput" name="address[postcode]" class="js-input" value="INVALID">
                  <button type="submit" class="js-find" name="lookup" value="find">Find address</button>
                </div>
                ${address}
              </fieldset>
            </form>
            <footer style="height: 1000px"></footer>
          `;
    }

    test.beforeEach(async ({ page }) => {
      await page.setContent(
        `<body class="govuk-frontend-supported"><main class="js-body">${body("Initial errors")}</main></body>`,
      );
      await page.addScriptTag({ path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js") });
      await page.evaluate(() => {
        window.OLCS = { logger: { debug() {}, verbose() {} } };
      });
      for (const component of ["eventEmitter", "formHelper", "normaliseResponse", "postcodeSearch", "errorSummary"]) {
        await page.addScriptTag({ path: path.join(__dirname, `../assets/_js/components/${component}.js`) });
      }
      await page.addScriptTag({
        path: path.join(
          __dirname,
          "../node_modules/govuk-frontend/dist/govuk/components/error-summary/error-summary.bundle.js",
        ),
      });
      await page.evaluate(() => {
        OLCS.errorSummary(window.GOVUKFrontend.ErrorSummary);
        new window.GOVUKFrontend.ErrorSummary(document.querySelector(".govuk-error-summary"));
        OLCS.postcodeSearch({ container: ".js-postcode-search" });
      });
    });

    async function lookup(page, content) {
      await page.evaluate((html) => {
        OLCS.submitForm = (options) => options.success(`<main class="js-body">${html}</main>`);
      }, content);
      await page.getByRole("button", { name: "Find address" }).click();
    }

    test("focuses and scrolls to each refreshed summary and enables its links", async ({ page }) => {
      for (const message of ["Enter a valid postcode", "No addresses found"]) {
        await lookup(page, body(message));
        const summary = page.locator(".govuk-error-summary");
        await expect(summary).toHaveCount(1);
        await expect(summary).toContainText(message);
        await expect(summary).toBeFocused();
        await expect(summary).toBeInViewport({ ratio: 1 });
        await page.getByRole("link", { name: message }).click();
        await expect(page.locator("#postcodeInput")).toBeFocused();

        await page.evaluate(() => {
          OLCS.eventEmitter.emit("render");
          OLCS.eventEmitter.emit("render", [$(".js-body")]);
        });
        await expect(page.locator("#postcodeInput")).toBeFocused();
      }
    });

    test("does not move focus when an unrelated region is refreshed", async ({ page }) => {
      await page.locator("#postcodeInput").focus();
      await page.evaluate(() => {
        $("<aside></aside>").appendTo("body");
        OLCS.formHelper.render("aside", "Updated content");
      });
      await expect(page.locator("#postcodeInput")).toBeFocused();
    });

    for (const [description, address, target] of [
      [
        "address selection",
        '<div class="address__select"><select aria-label="Select address"><option>1 Test Street</option></select></div>',
        "select",
      ],
      [
        "manual address entry",
        '<div class="field"><label for="addressLine1">Address line 1</label><input id="addressLine1" type="text"></div>',
        "#addressLine1",
      ],
    ]) {
      test(`preserves focus on ${description} after a successful lookup`, async ({ page }) => {
        await lookup(page, body("Enter a valid postcode"));
        await lookup(page, body("", address));
        await expect(page.locator(".govuk-error-summary")).toHaveCount(0);
        await expect(page.locator(target)).toBeFocused();
      });
    }
  });
}

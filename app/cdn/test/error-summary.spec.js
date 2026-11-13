// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");

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
}

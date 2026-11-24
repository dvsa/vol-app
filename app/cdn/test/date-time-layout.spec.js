// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const sass = require("sass");

for (const app of ["internal", "selfserve"]) {
  test(`${app} date and time wrap together when space is limited`, async ({ page }) => {
    const css = sass.compile(path.join(__dirname, `../assets/_styles/themes/${app}.scss`), {
      logger: sass.Logger.silent,
    }).css;

    await page.setContent(`
      <div id="container" style="width: 800px">
        <div class="date-time-input">
          <div class="govuk-date-input">
            <div class="govuk-date-input__item"><div class="govuk-form-group">
              <label class="govuk-label govuk-date-input__label" for="day">Day</label>
              <input id="day" class="govuk-input govuk-date-input__input govuk-input--width-2" type="text">
            </div></div>
            <div class="govuk-date-input__item"><div class="govuk-form-group">
              <label class="govuk-label govuk-date-input__label" for="month">Month</label>
              <input id="month" class="govuk-input govuk-date-input__input govuk-input--width-2" type="text">
            </div></div>
            <div class="govuk-date-input__item"><div class="govuk-form-group">
              <label class="govuk-label govuk-date-input__label" for="year">Year</label>
              <input id="year" class="govuk-input govuk-date-input__input govuk-input--width-4" type="text">
            </div></div>
          </div>
          <div class="date-time-input__time"><select aria-label="Hour"><option>00</option></select>:<select aria-label="Minute"><option>00</option></select></div>
        </div>
      </div>
    `);
    await page.addStyleTag({ content: css });

    const date = page.locator(".govuk-date-input");
    const time = page.locator(".date-time-input__time");
    let dateBox = await date.boundingBox();
    let timeBox = await time.boundingBox();
    expect(dateBox).not.toBeNull();
    expect(timeBox).not.toBeNull();
    expect(timeBox.x).toBeGreaterThanOrEqual(dateBox.x + dateBox.width);
    const dayBox = await page.locator("#day").boundingBox();
    const desktopHourBox = await page.getByLabel("Hour").boundingBox();
    expect(Math.abs(desktopHourBox.y + desktopHourBox.height - dayBox.y - dayBox.height)).toBeLessThan(2);

    await page.locator("#container").evaluate((container) => (container.style.width = "280px"));
    dateBox = await date.boundingBox();
    timeBox = await time.boundingBox();
    expect(timeBox.y).toBeGreaterThanOrEqual(dateBox.y + dateBox.height);
    const hourBox = await page.getByLabel("Hour").boundingBox();
    const minuteBox = await page.getByLabel("Minute").boundingBox();
    expect(Math.abs(hourBox.y - minuteBox.y)).toBeLessThan(2);
  });
}

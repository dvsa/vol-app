// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");

function address(name) {
  const title = name[0].toUpperCase() + name.slice(1);
  return `
    <fieldset class="address js-postcode-search" data-group="${name}Address">
      <legend>${title} address</legend>
      <div data-group="${name}Address[searchPostcode]">
        <div class="field inline">
          <label for="${name}Postcode">${title} address postcode</label>
          <input type="text" id="${name}Postcode" name="${name}Address[searchPostcode][postcode]" class="js-input">
        </div>
        <div class="field inline">
          <button type="submit" class="js-find" name="${name}Address[searchPostcode][search]" value="search">Find ${name} address</button>
        </div>
        <div class="field js-visible"><p class="hint"><a href="#">Enter ${name} address manually</a></p></div>
      </div>
      <div class="field">
        <label for="${name}AddressLine1">Address line 1</label>
        <input type="text" id="${name}AddressLine1" name="${name}Address[addressLine1]">
      </div>
    </fieldset>`;
}

test.beforeEach(async ({ page }) => {
  await page.setContent(`
    <style>.js-visible { display: none; } .js-enabled .js-visible { display: block; }</style>
    <main class="js-body"><form>${address("home")}${address("work")}</form></main>`);
});

test("provides distinct postcode labels and manual fields without JavaScript", async ({ page }) => {
  for (const name of ["Home", "Work"]) {
    const group = page.getByRole("group", { name: `${name} address`, exact: true });
    await expect(group.getByLabel(`${name} address postcode`)).toBeVisible();
    await expect(group.getByRole("button", { name: `Find ${name.toLowerCase()} address` })).toBeVisible();
    await expect(group.locator(`#${name.toLowerCase()}AddressLine1`)).toBeVisible();
    await expect(group.locator("fieldset")).toHaveCount(0);
  }
});

async function enablePostcodeSearch(page) {
  await page.evaluate(() => document.documentElement.classList.add("js-enabled"));
  await page.addScriptTag({ path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js") });
  await page.evaluate(() => {
    window.OLCS = { logger: { debug() {}, verbose() {} } };
  });
  for (const component of ["eventEmitter", "formHelper", "normaliseResponse", "postcodeSearch"]) {
    await page.addScriptTag({ path: path.join(__dirname, `../assets/_js/components/${component}.js`) });
  }
  await page.evaluate(() => {
    OLCS.postcodeSearch({ container: ".js-postcode-search" });
    OLCS.eventEmitter.emit("render");
  });
}

test("manual entry affects only the selected address", async ({ page }) => {
  await enablePostcodeSearch(page);
  await expect(page.locator("#homeAddressLine1")).toBeHidden();
  await expect(page.locator("#workAddressLine1")).toBeHidden();
  await page.getByRole("link", { name: "Enter home address manually" }).click();
  await expect(page.locator("#homeAddressLine1")).toBeVisible();
  await expect(page.locator("#workAddressLine1")).toBeHidden();
  await expect(page.getByRole("link", { name: "Enter work address manually" })).toBeVisible();
});

test("lookup submits the chosen address rather than the other one", async ({ page }) => {
  await enablePostcodeSearch(page);
  await page.evaluate(() => {
    OLCS.submitForm = () => {};
  });

  for (const name of ["home", "work"]) {
    await page.getByRole("button", { name: `Find ${name} address` }).click();
    await expect(page.locator("input.form__action")).toHaveAttribute("name", `${name}Address[searchPostcode][search]`);
  }
});

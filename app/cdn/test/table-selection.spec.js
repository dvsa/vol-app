// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");

test("selection actions count rows, not select-all or disabled controls", async ({ page }) => {
  await page.setContent(`
    <form class="table__form">
      <div class="govuk-button-group">
        <button type="button" class="one">Edit</button>
        <button type="button" class="multiple">Delete</button>
      </div>
      <table><thead><tr><th><input type="checkbox" name="checkall"></th></tr></thead>
      <tbody>
        <tr><td><input type="checkbox" name="id[]" value="1"></td></tr>
        <tr><td><input type="checkbox" name="id[]" value="2"></td></tr>
        <tr><td><input type="checkbox" name="id[]" value="3" disabled></td></tr>
      </tbody></table>
    </form>
  `);
  await page.addScriptTag({ path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js") });
  for (const component of ["eventEmitter", "tableCheckAll", "conditionalButton"]) {
    await page.addScriptTag({ path: path.join(__dirname, `../assets/_js/components/${component}.js`) });
  }
  await page.evaluate(() => {
    window.OLCS.logger = { debug() {}, verbose() {} };
    window.OLCS.tableCheckAll();
    window.OLCS.conditionalButton({
      form: ".table__form",
      selector: ".one",
      predicate: (count, callback) => callback(count === 1),
    });

    window.OLCS.conditionalButton({
      form: ".table__form",
      selector: ".multiple",
      predicate: (count, callback) => callback(count >= 1),
    });
    window.OLCS.eventEmitter.emit("render");
  });

  const edit = page.getByRole("button", { name: "Edit" });
  const remove = page.getByRole("button", { name: "Delete" });
  const rows = page.locator("tbody input");
  const all = page.locator('input[name="checkall"]');
  await expect(edit).toBeDisabled();
  await expect(remove).toBeDisabled();
  await rows.nth(0).check();
  await expect(edit).toBeEnabled();
  await expect(remove).toBeEnabled();
  await all.check();
  await expect(rows.nth(1)).toBeChecked();
  await expect(rows.nth(2)).not.toBeChecked();
  await expect(edit).toBeDisabled();
  await expect(remove).toBeEnabled();
  await all.uncheck();
  await expect(edit).toBeDisabled();
  await expect(remove).toBeDisabled();
});

test("labels and select-all operate only on their own table", async ({ page }) => {
  await page.setContent(`
    <form class="table__form">
      <table><thead><tr><th><input id="all-first" type="checkbox" name="checkall"><label for="all-first">All first</label></th></tr></thead>
      <tbody><tr><td><input id="row-first" type="checkbox" name="id[]" value="7"><label for="row-first">First row</label></td></tr></tbody></table>
    </form>
    <form class="table__form">
      <table><thead><tr><th><input id="all-second" type="checkbox" name="checkall"><label for="all-second">All second</label></th></tr></thead>
      <tbody><tr><td><input id="row-second" type="checkbox" name="id[]" value="7"><label for="row-second">Second row</label></td></tr></tbody></table>
    </form>
  `);
  await page.addScriptTag({ path: path.join(__dirname, "../node_modules/jquery/dist/jquery.js") });
  await page.addScriptTag({ path: path.join(__dirname, "../assets/_js/components/tableCheckAll.js") });
  await page.evaluate(() => {
    window.OLCS.logger = { debug() {}, verbose() {} };
    window.OLCS.tableCheckAll();
  });

  await page.getByText("Second row").click();
  await expect(page.locator("#row-second")).toBeChecked();
  await expect(page.locator("#row-first")).not.toBeChecked();
  await page.getByText("All first").click();
  await expect(page.locator("#row-first")).toBeChecked();
  await page.getByText("All first").click();
  await expect(page.locator("#row-first")).not.toBeChecked();
  await expect(page.locator("#row-second")).toBeChecked();
});

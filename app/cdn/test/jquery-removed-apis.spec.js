// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");

const root = path.join(__dirname, "..");

async function load(page, html, components) {
  await page.setContent(html);
  await page.addScriptTag({ path: path.join(root, "node_modules", "jquery", "dist", "jquery.js") });
  await page.evaluate(() => {
    window.OLCS = {
      logger: { debug() {}, verbose() {} },
      eventEmitter: { on() {}, emit() {} },
      formHelper: Object.assign((group) => $(group), {
        containsErrors: () => false,
        containsWarnings: () => false,
      }),
    };
  });
  for (const name of components) {
    await page.addScriptTag({ path: path.join(root, "assets", "_js", "components", name + ".js") });
  }
}

test("cascadeForm hides a fieldset when its rule function returns false", async ({ page }) => {
  await load(page, "<form><fieldset id='fs'><input name='a'></fieldset></form>", ["cascadeForm"]);

  await page.evaluate(() => {
    OLCS.cascadeForm({ form: "form", rulesets: { "#fs": () => false } });
  });

  await expect(page.locator("#fs")).toBeHidden();
});

test("cascadeInput accepts a process function and rejects anything else", async ({ page }) => {
  await load(page, "<select id='src'><option value='1'>one</option></select><input id='dest' type='text'>", [
    "cascadeInput",
  ]);

  const result = await page.evaluate(() => {
    OLCS.cascadeInput({ source: "#src", dest: "#dest", process: (value, done) => done({ value: "got " + value }) });
    $("#src").trigger("change");
    let error = null;
    try {
      OLCS.cascadeInput({ source: "#src", dest: "#dest", process: "nope" });
    } catch (e) {
      error = e.message;
    }
    return { dest: $("#dest").val(), error };
  });

  expect(result.dest).toBe("got 1");
  expect(result.error).toContain("process");
});

test("complexPredicate accepts a plain array of allowed values", async ({ page }) => {
  await load(page, "<p></p>", ["complexPredicate"]);

  const result = await page.evaluate(() => {
    const check = OLCS.complexPredicate(["edit", "view"]);
    const results = [];
    check(1, (enabled) => results.push(enabled), [$("<input data-action='edit'>")[0]]);
    check(1, (enabled) => results.push(enabled), [$("<input data-action='delete'>")[0]]);
    return results;
  });

  expect(result).toEqual([true, false]);
});

test("normaliseResponse falls back to the body heading when the title is blank", async ({ page }) => {
  await load(page, "<p></p>", ["normaliseResponse"]);

  const title = await page.evaluate(() => {
    let title = null;
    const html =
      "<div class='js-response'><div class='js-title'>  </div>" +
      "<div class='js-body'><div class='js-content'><h1>Real title</h1></div></div></div>";
    OLCS.normaliseResponse(function (response) {
      title = response.title;
    })(html);
    return title;
  });

  expect(title).toBe("Real title");
});

test("tableRows stops the context menu while ctrl is held", async ({ page }) => {
  await load(page, "<table><tbody><tr><td><input type='checkbox'></td></tr></tbody></table>", ["tableRows"]);

  await page.evaluate(() => OLCS.tableRows());
  await page.keyboard.down("Control");
  const prevented = await page.evaluate(() => {
    const event = $.Event("contextmenu");
    $("table.js-rows td").trigger(event);
    return event.isDefaultPrevented();
  });

  expect(prevented).toBe(true);
});

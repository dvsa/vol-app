// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");
const fs = require("node:fs");

const root = path.join(__dirname, "..");

for (const template of ["messages-new", "messages-view"]) {
  test(`${template} clears stale summary and inline errors after removing an attachment`, async ({ page }) => {
    const source = fs.readFileSync(path.join(root, `../selfserve/module/Olcs/view/${template}.phtml`), "utf8");
    function render(errors, message) {
      // Substitute server-rendered content while preserving the real template's AJAX boundaries.
      return source.replace(/<\?php[\s\S]*?\?>/g, (block) => {
        if (block.includes("'partials/page-header-simple'")) {
          return `<div class="page-header">
            ${errors ? '<div class="govuk-error-summary"><h2>There is a problem</h2><p>Enter a message</p></div>' : ""}
            <h1 class="js-title">Messages</h1>
          </div>`;
        }
        if (block.includes("$this->form($this->form)")) {
          return `<form>
            <div class="${errors ? "validation-wrapper" : ""}">
              ${errors ? "<ul><li>Enter a message</li></ul>" : ""}
              <label for="message">Message</label><textarea id="message">${message}</textarea>
            </div>
            <button class="file__remove" name="file[remove]" value="1">Remove</button>
          </form>`;
        }
        return "";
      });
    }

    await load(page, render(true, ""), ["formHelper", "normaliseResponse", "fileUpload"]);
    await page.evaluate(() => {
      OLCS.modal = { isVisible: () => false };
      OLCS.fileUpload({ multiple: true });
    });
    await expect(page.locator(".govuk-error-summary")).toHaveCount(1);
    await page.locator("#message").fill("Corrected test message");
    await page.evaluate(
      (html) => {
        OLCS.submitForm = (options) => options.success(`<div class="js-response">${html}</div>`);
      },
      render(false, "Corrected test message"),
    );
    await page.getByRole("button", { name: "Remove" }).click();

    await expect(page.locator(".validation-wrapper")).toHaveCount(0);
    await expect(page.locator(".govuk-error-summary")).toHaveCount(0);
    await expect(page.getByRole("heading", { name: "Messages", exact: true })).toHaveCount(1);
    await expect(page.locator("#message")).toHaveValue("Corrected test message");

    await page.evaluate(
      (html) => {
        OLCS.submitForm = (options) => options.success(`<div class="js-response">${html}</div>`);
      },
      render(true, ""),
    );
    await page.getByRole("button", { name: "Remove" }).click();
    await expect(page.locator(".govuk-error-summary")).toHaveCount(1);
    await expect(page.locator(".validation-wrapper")).toHaveCount(1);
  });
}

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

test("normaliseResponse detects the GOV.UK error summary in an AJAX response", async ({ page }) => {
  await load(page, "<div class='modal__wrapper'></div>", ["normaliseResponse"]);

  const hasValidationErrors = await page.evaluate(() => {
    let result = false;
    OLCS.normaliseResponse((response) => {
      result = response.hasValidationErrors;
    })("<div class='govuk-error-summary'><h2>Error</h2></div>");
    return result;
  });

  expect(hasValidationErrors).toBe(true);
});

test("formHelper detects and clears the GOV.UK error summary", async ({ page }) => {
  await load(
    page,
    "<main><div class='govuk-error-summary'></div><div class='validation-wrapper'><ul><li>Error</li></ul></div></main>",
    ["formHelper"],
  );

  const result = await page.evaluate(() => {
    const containsErrors = OLCS.formHelper.containsErrors($("main"));
    OLCS.formHelper.clearErrors($("main"));
    return {
      containsErrors,
      summaryCount: $("main .govuk-error-summary").length,
      wrapperClass: $("main .validation-wrapper").length,
      wrapperErrorListCount: $("main .validation-wrapper ul").length,
    };
  });

  expect(result).toEqual({
    containsErrors: true,
    summaryCount: 0,
    wrapperClass: 0,
    wrapperErrorListCount: 0,
  });
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

// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("node:path");

const root = path.join(__dirname, "..");
const inline = path.join(root, "..", "..", "lib", "olcs-common", "Common", "src", "Common", "assets", "js", "inline");

async function lookup(page, script, input, nameId, url) {
  await page.route("http://localhost:9/**", (route) =>
    route.fulfill({
      json: { id: 1, name: "Bob Smith" },
      headers: { "access-control-allow-origin": "*" },
    }),
  );
  await page.setContent(`<input id="${input}" data-lookup-url="${url}">`);
  await page.addScriptTag({ path: path.join(root, "node_modules", "jquery", "dist", "jquery.js") });
  await page.addScriptTag({ path: path.join(inline, script) });
  await page.locator("#" + input).fill("1");
  await page.locator("#" + input).dispatchEvent("change");
  await expect(page.locator("#" + nameId)).toHaveText("Bob Smith");
}

test("operator merge shows the looked-up operator name", async ({ page }) => {
  await lookup(page, "operator-merge.js", "toOperatorId", "toOperatorName", "http://localhost:9/operator/lookup/");
});

test("transport manager merge shows the looked-up name", async ({ page }) => {
  await lookup(page, "tm-merge.js", "toTmId", "toTmName", "http://localhost:9/tm/lookup/");
});

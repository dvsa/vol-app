const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

const script = fs.readFileSync(
  path.join(__dirname, "../../internal/module/Admin/assets/js/inline/forms/translation-key-modal.js"),
  "utf8",
);
const context = { OLCS: {}, $: () => {} };
vm.runInNewContext(script, context);

test("rich values come from contentJson, not rendered HTML", () => {
  assert.equal(typeof context.OLCS.translationKeyModalData?.value, "function");
  const row = {
    translatedText: "<p>Rendered</p>",
    contentJson: { blocks: [{ type: "paragraph", data: { text: "Source" } }] },
  };
  assert.equal(context.OLCS.translationKeyModalData.value(row, "editorjs"), JSON.stringify(row.contentJson));
  assert.equal(context.OLCS.translationKeyModalData.value(row, "text"), "<p>Rendered</p>");
});

test("promotion requires a preview for every existing language", () => {
  assert.equal(typeof context.OLCS.translationKeyModalData?.canPromote, "function");
  const rows = [{ language: { isoCode: "en_GB" } }, { language: { isoCode: "cy_GB" } }];
  const preview = { en_GB: { blocks: [] } };
  assert.equal(context.OLCS.translationKeyModalData.canPromote(rows, preview), false);
  assert.equal(context.OLCS.translationKeyModalData.canPromote(rows, { ...preview, cy_GB: { blocks: [] } }), true);
});

const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

test("AJAX submission waits for EditorJS before serializing", async () => {
  let finishFlush;
  let content = "old";
  const calls = [];
  const form = {
    find(selector) {
      return { length: selector === ".editorjs-container" ? 1 : 0 };
    },
    serialize() {
      return content;
    },
    attr(name) {
      return name === "action" ? "/save" : "post";
    },
  };
  const jquery = () => ({ hasClass: () => false });
  const context = {
    document: {},
    window: { location: { pathname: "/save" }, jQuery: jquery },
    OLCS: {
      editorjsFlush: () =>
        new Promise((resolve) => {
          finishFlush = resolve;
        }),
      ajax: (options) => {
        calls.push(options);
        return options;
      },
      logger: { debug() {} },
    },
  };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, "../assets/_js/components/submitForm.js"), "utf8"), context);

  const submission = context.OLCS.submitForm({ form, disable: false, success() {} });
  assert.equal(calls.length, 0);
  content = "new";
  finishFlush();
  await submission;
  assert.equal(calls[0].data, "new");
});

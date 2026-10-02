const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

test("replacing a modal leaves one submit handler", () => {
  const handlers = [];
  const context = {
    document: {},
    window: { jQuery: () => {} },
    OLCS: {
      eventEmitter: { on() {}, once() {} },
      modal: { show() {} },
      formHandler() {
        const handler = {
          active: true,
          unbind() {
            this.active = false;
          },
        };
        handlers.push(handler);
        return handler;
      },
    },
  };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, "../assets/_js/components/modalForm.js"), "utf8"), context);
  context.OLCS.modalForm({ body: "first" });
  context.OLCS.modalForm({ body: "second" });
  assert.equal(handlers.filter((handler) => handler.active).length, 1);
});

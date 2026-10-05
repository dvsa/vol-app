const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

function setupEditor({
  initialValue = "",
  optIn = true,
  outputData = { blocks: [{ type: "paragraph", data: { text: "New" } }] },
} = {}) {
  let editorOptions;
  const events = [];
  const hiddenInput = {
    value: initialValue,
    disabled: true,
    hasAttribute(name) {
      return optIn && name === "data-enable-on-editor-change";
    },
    dispatchEvent(event) {
      events.push(event.type);
      this.disabled = false;
    },
  };
  const container = {
    initialized: false,
    data(name, value) {
      if (name === "editorjs-initialized") {
        if (arguments.length === 2) this.initialized = value;
        return this.initialized;
      }
      return name === "element-name" ? "fields[translationsArray][en_GB]" : "default";
    },
    find(selector) {
      return selector === ".editorjs-editor" ? { length: 1, attr: () => "editor-en_GB" } : { val: () => initialValue };
    },
  };
  const jquery = (selector) => {
    if (selector === ".editorjs-container")
      return {
        each(callback) {
          callback.call(container);
        },
      };
    if (selector === container) return container;
    return {
      off() {
        return this;
      },
      on() {
        return this;
      },
    };
  };
  const context = {
    document: {
      getElementById() {
        return {};
      },
      querySelector() {
        return hiddenInput;
      },
    },
    window: { jQuery: jquery },
    Event: class {
      constructor(type) {
        this.type = type;
      }
    },
    EditorJS: class {
      constructor(options) {
        editorOptions = options;
        this.isReady = new Promise(() => {});
      }
      save() {
        return Promise.resolve(outputData);
      }
    },
    OLCS: { eventEmitter: { on() {} } },
  };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, "../assets/_js/components/editorjs.js"), "utf8"), context);
  context.OLCS.editorjs();
  return { context, editorOptions, hiddenInput, events };
}

test("EditorJS change signals an opt-in hidden input after saving", async () => {
  const { editorOptions, events } = setupEditor();
  editorOptions.onChange();
  await Promise.resolve();
  await Promise.resolve();
  assert.deepEqual(events, ["change"]);
});

test("immediate save before onChange enables the newly authored language", async () => {
  const { context, hiddenInput, events } = setupEditor({ initialValue: '{"blocks":[]}' });
  await context.OLCS.editorjsFlush();
  assert.equal(JSON.parse(hiddenInput.value).blocks[0].data.text, "New");
  assert.equal(hiddenInput.disabled, false);
  assert.deepEqual(events, ["change"]);
});

for (const optIn of [true, false]) {
  test(`clean flush preserves the input and emits no signal (opt-in: ${optIn})`, async () => {
    const blocks = [{ type: "paragraph", data: { text: "Existing" } }];
    const initialValue = JSON.stringify({ time: 1, blocks });
    const { context, hiddenInput, events } = setupEditor({
      initialValue,
      optIn,
      outputData: { time: 2, blocks },
    });
    await context.OLCS.editorjsFlush();
    assert.equal(hiddenInput.value, initialValue);
    assert.equal(hiddenInput.disabled, true);
    assert.deepEqual(events, []);
  });
}

test("changed content without opt-in flushes without a change signal", async () => {
  const { context, hiddenInput, events } = setupEditor({ optIn: false });
  await context.OLCS.editorjsFlush();
  assert.equal(JSON.parse(hiddenInput.value).blocks[0].data.text, "New");
  assert.equal(hiddenInput.disabled, true);
  assert.deepEqual(events, []);
});

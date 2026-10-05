const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

function setupModal({ baseUrl = "/keys/", addEdit = "edit", searchResults = [] } = {}) {
  const inputs = {};
  const changeHandlers = {};
  const handlers = {};
  const requests = [];
  const elements = [];
  const errors = [];
  const languages = ["en_GB", "cy_GB"];
  const values = {
    "#addedit": addEdit,
    "#jsonUrl": baseUrl,
    "#resultsKey": "translationKeyTexts",
    "#longTextMode": "1",
    "#format": "text",
    "#id": "42",
  };
  function wrapper(selector, attrs = {}) {
    const object = {
      selector,
      attrs,
      children: [],
      val(value) {
        if (arguments.length) {
          values[selector] = value;
          return this;
        }
        return values[selector];
      },
      on(events, handler) {
        handlers[selector + ":" + events] = handler;
        if (attrs["data-element-name"] && events === "input change") {
          changeHandlers[attrs["data-element-name"]] = handler;
        }
        return this;
      },
      off() {
        return this;
      },
      append(child) {
        this.children.push(child);
        if (child && child.attrs && child.attrs.type === "hidden") inputs[child.attrs.name] = child.attrs;
        return this;
      },
      prepend() {
        return this;
      },
      empty() {
        return this;
      },
      attr(name, value) {
        attrs[name] = value;
        return this;
      },
      data(name) {
        return name === "language" ? attrs["data-language"] : "en_GB";
      },
      first() {
        return this;
      },
      addClass(value) {
        attrs.class = value;
        return this;
      },
      removeClass(value) {
        if (selector === "#translationEditorError" && value === "js-hidden") errors.push(this.textContent);
        return this;
      },
      text(value) {
        this.textContent = value;
        return this;
      },
      prop() {
        return this;
      },
      each(callback) {
        if (selector === ".langFields")
          languages.forEach((language) =>
            callback.call(wrapper("#language-" + language, { "data-language": language })),
          );
        return this;
      },
    };
    if (selector.startsWith("<")) elements.push(object);
    return object;
  }
  const jquery = (selector, attrs) => {
    if (typeof selector === "function") {
      selector();
      return;
    }
    return typeof selector === "object" ? selector : wrapper(selector, attrs);
  };
  jquery.get = (url, data, callback) => {
    if (typeof data === "function") callback = data;
    requests.push(url);
    if (url.endsWith("languages")) callback({ languages: { en_GB: { label: "English" }, cy_GB: { label: "Welsh" } } });
    if (url.endsWith("xhrsearch")) callback({ results: searchResults });
    if (url === baseUrl + "gettext/42")
      callback({
        format: "editorjs",
        description: "Page",
        translationKeyTexts: [
          { language: { isoCode: "en_GB" }, contentJson: { blocks: [{ type: "paragraph", data: { text: "Hello" } }] } },
        ],
      });
    return { fail() {} };
  };
  const context = {
    $: jquery,
    document: {
      getElementById(id) {
        const name = "fields[translationsArray][" + id.replace("input-", "") + "]";
        return {
          prop(property, value) {
            inputs[name][property] = value;
          },
        };
      },
    },
    OLCS: { eventEmitter: { emit() {} } },
  };
  vm.runInNewContext(
    fs.readFileSync(
      path.join(__dirname, "../../internal/module/Admin/assets/js/inline/forms/translation-key-modal.js"),
      "utf8",
    ),
    context,
  );
  return {
    inputs,
    changeHandlers,
    requests,
    elements,
    errors,
    handlers,
    search(term) {
      values["#existingMarkupSearch"] = term;
      handlers["#existingMarkupSearch:input"].call(wrapper("#existingMarkupSearch"));
    },
  };
}

for (const baseUrl of [
  "/admin/long-text/",
  "/admin/editable-translations/",
  "/tenant%20one/admin/long-text/",
  "/%/example.test/",
]) {
  test("picker preserves the server route and uses text for labels: " + baseUrl, () => {
    const item = {
      id: 42,
      translationKey: "markup-<img src=x onerror=alert(1)>",
      description: "<b>Page & description</b>",
    };
    const modal = setupModal({ baseUrl, addEdit: "add", searchResults: [item] });
    modal.search("markup");
    assert.deepEqual(modal.requests, [baseUrl + "languages", baseUrl + "xhrsearch"]);
    const link = modal.elements.find((element) => element.attrs.class === "govuk-link js-modal-ajax");
    assert.equal(link.selector, "<a>");
    assert.equal(link.attrs.href, baseUrl + "editkey/42");
    assert.equal(link.textContent, item.translationKey + " — " + item.description);
    assert.deepEqual(link.children, []);
    assert.ok(modal.elements.every((element) => /^<\w+>$/.test(element.selector)));
  });
}

test("invalid base paths show an error and stop initialization before requests or links", () => {
  for (const baseUrl of [
    "javascript:alert(1)//",
    "data:text/html,test/",
    "https://example.test/admin/long-text/",
    "//example.test/",
    "/\\example.test/",
    "/admin\\long-text/",
    "admin/long-text/",
    "",
    null,
    "/admin/long-text",
    "/admin/long-text/?next=x",
    "/admin/long-text/#fragment",
    " /admin/long-text/",
    "/admin/long text/",
    "/admin/\tlong-text/",
    "/admin/\u0000long-text/",
    "/admin/\u007flong-text/",
    "/admin/\u00a0long-text/",
  ]) {
    for (const addEdit of ["add", "edit"]) {
      const modal = setupModal({ baseUrl, addEdit });
      assert.deepEqual(modal.requests, [], JSON.stringify(baseUrl));
      assert.deepEqual(modal.elements, []);
      assert.deepEqual(modal.handlers, {});
      assert.equal(modal.errors.length, 1);
      assert.match(modal.errors[0], /translation URL is invalid/i);
    }
  }
});

test("a trailing newline cannot bypass the base path guard", () => {
  for (const suffix of ["\n", "\r", "\r\n", "\u2028", "\u2029"]) {
    const modal = setupModal({ baseUrl: "/admin/long-text/" + suffix, addEdit: "add" });
    assert.deepEqual(modal.requests, [], JSON.stringify(suffix));
    assert.deepEqual(modal.elements, []);
    assert.deepEqual(modal.handlers, {});
    assert.equal(modal.errors.length, 1);
  }
});

test("editing rich content submits existing languages but omits absent languages", () => {
  const { inputs, changeHandlers } = setupModal();
  assert.equal(inputs["fields[translationsArray][en_GB]"].disabled, false);
  assert.equal(inputs["fields[translationsArray][cy_GB]"].disabled, true);
  changeHandlers["fields[translationsArray][cy_GB]"]();
  assert.equal(inputs["fields[translationsArray][cy_GB]"].disabled, false);
});

async function flushAbsentLanguage(modal, outputData) {
  const name = "fields[translationsArray][cy_GB]";
  const input = modal.inputs[name];
  input.hasAttribute = (attribute) => Object.hasOwn(input, attribute);
  input.dispatchEvent = () => modal.changeHandlers[name]();
  const data = { "element-name": name, "tools-profile": "default" };
  const container = {
    data(key, value) {
      if (arguments.length === 2) data[key] = value;
      return data[key];
    },
    find(selector) {
      return selector === ".editorjs-editor" ? { length: 1, attr: () => "editor-cy_GB" } : { val: () => input.value };
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
        return input;
      },
    },
    window: { jQuery: jquery },
    Event: class {},
    EditorJS: class {
      constructor() {
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
  await context.OLCS.editorjsFlush();
  return input;
}

test("untouched absent language stays omitted after flushing the actual modal value", async () => {
  const input = await flushAbsentLanguage(setupModal(), { blocks: [], time: 2 });
  assert.equal(input.disabled, true);
  assert.deepEqual(JSON.parse(input.value).blocks, []);
});

test("newly authored absent language is included on immediate flush before onChange", async () => {
  const output = { blocks: [{ type: "paragraph", data: { text: "New Welsh content" } }] };
  const input = await flushAbsentLanguage(setupModal(), output);
  assert.equal(input.disabled, false);
  assert.deepEqual(JSON.parse(input.value), output);
});

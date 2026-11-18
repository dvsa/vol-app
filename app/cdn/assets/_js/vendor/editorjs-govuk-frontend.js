/*
 * Browser entry point for the GOV.UK Editor.js tools used by Long Text.
 *
 * Browserify exposes this module as window.VolEditorJsGovuk. Existing EditorJS
 * consumers continue to use their current UMD tools and JSON schema.
 */
var govukEditor = require("@vitriltd/editor-js-govuk-frontend");

class LongTextParagraph extends govukEditor.GovukParagraph {
  renderEdit() {
    var element = super.renderEdit();

    if (this.config.placeholder) {
      element.setAttribute("data-placeholder", this.config.placeholder);
    }

    return element;
  }

  extractData() {
    var data = super.extractData();

    // VOL-7275 deliberately excludes font-size controls.
    data.size = "body";

    return data;
  }

  renderSettings() {
    return [];
  }
}

// The library's list only keeps <br> in an item, so a save strips anything else.
// Seeded Long Text items also use <strong>, <p> and nested lists, so keep those.
// EditorJS still adds the inline tools' tags (bold, italic, link, underline) on top.
class LongTextList extends govukEditor.GovukList {
  static get sanitize() {
    return {
      items: {
        br: true,
        strong: {},
        p: { class: true },
        ul: { class: true },
        ol: { type: true, class: true },
        li: {},
      },
    };
  }
}

govukEditor.LongTextParagraph = LongTextParagraph;
govukEditor.LongTextList = LongTextList;

module.exports = govukEditor;

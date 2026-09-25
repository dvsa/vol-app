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

govukEditor.LongTextParagraph = LongTextParagraph;

module.exports = govukEditor;

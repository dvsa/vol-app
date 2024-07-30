/**
 * OLCS.radioButton
 *
 * grunt test:single --target=radioButton
 */

describe('OLCS.radionButton', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.radioButton;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('object');
  });

  describe("given a stubbed DOM", function(){
    beforeEach(function(){
      var template = "<div class=\"field\" id=\"stub\">" +
"            <h3>Yes / No with conditional content</h3>" +
"            <p class=\"form-hint\">Yes no conditional content should be displayed below the radio buttons.</p>" +
"            <fieldset class=\"radio-button__fieldset\">" +
"              <legend class=\"form-element__label form-label\">Does anyone you've named already have an operator's licence in any traffic area?</legend>" +
"              <div class=\"radio-button__container radio-button__container--inline\"\">" +
"                <input type=\"radio\" class=\"radio-button\" name=\"q1\" id=\"q1[Y]\" value=\"Y\" data-show-element=\"#q1-yes-content\">" +
"                <label class =\"radio-button__label\" for=\"q1[Y]\">Yes</label>" +
"              </div>" +
"              <div class=\"radio-button__container radio-button__container--inline\">" +
"                <input type=\"radio\" class=\"radio-button\" name=\"q1\" id=\"q1[N]\" value=\"N\" data-show-element=\"#q1-no-content\">" +
"                <label class=\"radio-button__label\" for=\"q1[N]\">No</label>" +
"              </div>" +
"                <div class=\"radio-button__hidden-content help__text\" id=\"q1-yes-content\" style=\"display:none\">" +
"                  <p>Yes content</p>" +
"                </div>" +
"                <div class=\"radio-button__hidden-content help__text\" id=\"q1-no-content\" style=\"display:none\">" +
"                  <p>No content</p>" +
"                </div>" +
"            </fieldset>" +
"          </div>";
      this.body = $('body');
      this.body.append(template);
      this.component.initialize();
    });

    afterEach(function(){
      $('#stub').remove();
    });

    describe("clicking the yes radio button", function(){

      beforeEach(function(){
        document.getElementById('q1[Y]').click();
      });
      it("should show the correct content", function(){
        expect($("#q1-yes-content").is(":visible")).to.be(true);
        expect($("#q1-no-content").is(":visible")).to.be(false);
      });

      describe("then clicking the no radio button", function(){
        beforeEach(function(){
          document.getElementById('q1[N]').click();
        });
        it("should show the correct content", function(){
          expect($("#q1-yes-content").is(":visible")).to.be(false);
          expect($("#q1-no-content").is(":visible")).to.be(true);
        });
      });
    });
  });

  describe("given a stubbed DOM, with a selected radio button", function(){
    beforeEach(function(){
      var template = "<div class=\"field\" id=\"stub\">" +
"            <h3>Yes / No with conditional content</h3>" +
"            <p class=\"form-hint\">Yes no conditional content should be displayed below the radio buttons.</p>" +
"            <fieldset class=\"radio-button__fieldset\">" +
"              <legend class=\"form-element__label form-label\">Does anyone you've named already have an operator's licence in any traffic area?</legend>" +
"              <div class=\"radio-button__container radio-button__container--inline\"\">" +
"                <input type=\"radio\" class=\"radio-button\" name=\"q1\" id=\"q1[Y]\" value=\"Y\" checked data-show-element=\"#q1-yes-content\">" +
"                <label class =\"radio-button__label\" for=\"q1[Y]\">Yes</label>" +
"              </div>" +
"              <div class=\"radio-button__container radio-button__container--inline\">" +
"                <input type=\"radio\" class=\"radio-button\" name=\"q1\" id=\"q1[N]\" value=\"N\" data-show-element=\"#q1-no-content\">" +
"                <label class=\"radio-button__label\" for=\"q1[N]\">No</label>" +
"              </div>" +
"                <div class=\"radio-button__hidden-content help__text\" id=\"q1-yes-content\" style=\"display:none\">" +
"                  <p>Yes content</p>" +
"                </div>" +
"                <div class=\"radio-button__hidden-content help__text\" id=\"q1-no-content\" style=\"display:none\">" +
"                  <p>No content</p>" +
"                </div>" +
"            </fieldset>" +
"          </div>";
      this.body = $('body');
      this.body.append(template);
    });

    afterEach(function(){
      $('#stub').remove();
    });

    describe("initalising the component", function(){

      beforeEach(function(){
        this.component.initialize();
      });

      it("the hidden content for the selected radio button should be visible", function(){
        expect($("#q1-yes-content").is(":visible")).to.be(true);
      });

    });
  });
});



/**
 * OLCS.modalLink
 *
 * grunt test:single --target=validation
 */

describe('OLCS.validation', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.validation;
    this.component.addEvent();
  });

  describe("given a stubbed DOM", function(){

    beforeEach(function() {
      $("body").append([
        '<form action="/foo" id="stub">',
          '<div class="checkbox__container">',
            '<input type="checkbox" class="checkbox checkbox--error" name="input[1]name" id="input[1]" value="Y"  data-js-validate="required" />',
            '<label class="checkbox__label checkbox__label--error" id="label[1]" for="BusinessDetails">Business details</label>',
            '<div class="checkbox__hidden-content help__text">',
              '<p>hidden content</p>',
            '</div>',
          '</div>',
        '</form>'
      ].join("\n"));


    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("removeErrorClasses function should remove the error classes from input and label", function(){
      beforeEach(function(){
        var input = document.getElementById("input[1]");
        $(input).prop("checked", true);
        this.component.removeErrorClasses(input);
      });

      afterEach(function(){
        $("#input[1]").prop("checked", false);
      });

      it("should remove the error classes", function(){
        expect($(document.getElementById("input[1]")).hasClass("checkbox--error")).to.be(false);
        expect($(document.getElementById("label[1]")).hasClass("checkbox__label--error")).to.be(false);
      });
    });

    describe("add event function should add removeErrorClasses change event", function(){
      beforeEach(function(){
        this.functionStub = sinon.spy(this.component, "removeErrorClasses");
        this.component.addEvent();
        //must be doen this was as jquery trigger events do not seem to work in phantomJS
        //see https://github.com/ariya/phantomjs/issues/13966
        document.getElementById("input[1]").dispatchEvent(new Event('change', { 'bubbles': true }));
      });

      afterEach(function(){
        $(document).off("change");
        this.functionStub.restore();
      });

      it("should call validation.removeErrorClasses function", function(){
        expect(this.functionStub.called).to.be(true);
      });

    });

  });
});

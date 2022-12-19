describe("OLCS.fileUpload", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.postcodeSearch;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("When initialised", function() {
    beforeEach(function() {
      this.component({
        container: ".address"
      });
    });

    describe("Given a stubbed DOM", function() {
      describe("Given the DOM is in a clean state", function() {
        beforeEach(function() {
          var stub = [
            "<fieldset class=file-uploader>",
              "<legend>Advertisement <span class='js-hidden'>(if applicable)</span>(optional)</legend>",
              "<div class=field>",
                "<ul class=attach-action__list>",
                  "<li class=attach-action>",
                    "<label class=attach-action__label>",
                      "<input type=file name='advertisements[file][file]' class='js-visually-hidden attach-action__input' id='advertisements[file][file]'>",
                    "</label>",
                  "</li>",
                "</ul>",
              "</div>",
              "<ul class='js-upload-list' data-group='advertisements[file][list]'></ul>",
              "<input type=hidden name='advertisements[file][__messages__]' id='advertisements[file][__messages__]>",
              "<button type=submit name='advertisements[file][upload]' class='govuk-button inline-upload js-upload' id='advertisements[file][upload]' value=Upload>Upload</button>",
            "</fieldset>"
          ].join("\n");

          this.body = $("body");
          this.body.append(stub);
        });

        afterEach(function() {
          $("#stub").remove();
        });

        describe("when initialised with 'multiple' options", function() {
          beforeEach(function() {
            this.options = {
              multiple: true
            };
            this.component(this.options);
          });

          it("should hide the submit button", function() {
            expect($('.js-upload').is(":visible")).to.equal(false);
          });

        });

      });
    });
  });
});

describe("OLCS.postcodeSearch", function() {
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
            "<div class=address id=stub data-group=a>",
              "<fieldset>",
                "<button type=submit name='address[searchPostcode][search]' class='govuk-button js-find' data-module='govuk-button'>Find address</button>",
                "<div class=field>",
                  "<p class=hint id=p1><a href=#>Manual</a></p>",
                "</div>",
              "</fieldset>",
              "<div class=field id=f1><input name='a[addressLine1]' type=text /></div>",
              "<div class=field id=f2><input name='a[addressLine2]' type=text /></div>",
            "</div>"
          ].join("\n");

          this.body = $("body");
          this.body.append(stub);
        });

        afterEach(function() {
          $("#stub").remove();
        });

        describe("When the component is notified of a new DOM", function() {
          beforeEach(function() {
            OLCS.eventEmitter.emit("render");
          });

          it("should hide the address fields", function() {
            expect($("#f1").is(":visible")).to.equal(false);
            expect($("#f2").is(":visible")).to.equal(false);
          });

          it("should show the manual address button", function() {
            expect($("#p1").is(":visible")).to.equal(true);
          });

          describe("When clicking the find button", function() {
            beforeEach(function() {
              this.submitForm = sinon.stub(OLCS, "submitForm");
              $(".address .js-find").click();
            });

            afterEach(function() {
              this.submitForm.restore();
            });

            it("should invoke a form AJAX submission", function() {
              expect(this.submitForm.callCount).to.equal(1);
            });

            describe("Given the AJAX request returns successfully", function() {
              beforeEach(function() {
                try {
                  this.submitForm.yieldTo("success", "some body");
                } catch (e) {
                  this.e = e;
                }
              });

              it("throws an error as there is no valid root selector found", function() {
                expect(this.e.message).to.equal("No valid root selector found");
              });
            });
          });

          describe("When clicking the enter address manually button", function() {
            beforeEach(function() {
              $(".address .hint a").click();
            });

            it("should show the address fields", function() {
              expect($("#f1").is(":visible")).to.equal(true);
              expect($("#f2").is(":visible")).to.equal(true);
            });

            it("should hide the manual address button", function() {
              expect($("#p1").is(":visible")).to.equal(false);
            });
          });
        });
      });

      describe("Given the the input field has an incorrectly formatted postcode", function() {
        beforeEach(function() {
          var stub = [
            "<div class=address id=stub>",
              "<fieldset>",
                "<input type=text class=js-input value=ls81an>",
                "<button type=submit name='address[searchPostcode][search]' class='govuk-button js-find' data-module='govuk-button'>Find address</button>",
              "</fieldset>",
            "</div>"
          ].join("\n");

          this.body = $("body");
          this.body.append(stub);
        });

        afterEach(function() {
          $("#stub").remove();
        });

        describe("When clicking the find button", function() {
          beforeEach(function() {
            this.submitForm = sinon.stub(OLCS, "submitForm");
            $(".address .js-find").click();
          });

          afterEach(function() {
            this.submitForm.restore();
          });

          it("should convert the input to uppercase and add a space", function() {
            expect($(".js-input").val()).to.be("LS8 1AN");
          });
        });
      });

      describe("Given some address fields have data", function() {
        beforeEach(function() {
          var stub = [
            "<div class=address id=stub data-group=a>",
              "<fieldset></fieldset>",
              "<fieldset>",
                "<div class=field>",
                  "<p class=hint><a href=#>Manual</a></p>",
                "</div>",
              "</fieldset>",
              "<div class=field id=f1><input name='a[addressLine1]' type=text /></div>",
              "<div class=field id=f2><input name='a[addressLine2]' type=text value='foo' /></div>",
            "</div>"
          ].join("\n");

          this.body = $("body");
          this.body.append(stub);
        });

        afterEach(function() {
          $("#stub").remove();
        });

        describe("When the component is notified", function() {
          beforeEach(function() {
            OLCS.eventEmitter.emit("render");
          });

          it("should show the address fields", function() {
            expect($("#f1").is(":visible")).to.equal(true);
            expect($("#f2").is(":visible")).to.equal(true);
          });

          it("should hide the manual address button", function() {
            expect($("#p1").is(":visible")).to.equal(false);
          });
        });
      });

      describe("Given the DOM is in a clean state but has no find button", function() {
        beforeEach(function() {
          var stub = [
            "<div class=address id=stub data-group=a>",
              "<fieldset>",
              "</fieldset>",
              "<div class=field id=f1><input name='a[addressLine1]' type=text /></div>",
              "<div class=field id=f2><input name='a[addressLine2]' type=text /></div>",
            "</div>"
          ].join("\n");

          this.body = $("body");
          this.body.append(stub);
        });

        afterEach(function() {
          $("#stub").remove();
        });

        describe("When the component is notified of a new DOM", function() {
          beforeEach(function() {
            OLCS.eventEmitter.emit("render");
          });

          it("should not hide the address fields", function() {
            expect($("#f1").is(":visible")).to.equal(true);
            expect($("#f2").is(":visible")).to.equal(true);
          });
        });
      });
    });
  });
});

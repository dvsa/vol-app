describe("OLCS.cascadeForm", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.cascadeForm;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed DOM", function() {
    beforeEach(function() {
      var template = [
        '<div id="stub">',
          '<form class="stub-form" action=/foo method=post>',
            '<fieldset class=f1 data-group=foo>',
              '<input class="i1" name="foo[bar]" value="" />',
            '</fieldset>',
            '<fieldset class=f2 data-group=bar>',
              '<input class="i2" name="bar[text]" value="bar-f2" />',
            '</fieldset>',
            '<fieldset class=f3 data-group=baz>',
              '<input name="baz[foo]" value="" />',
              '<label class=l1>',
                '<input type="radio" name="baz[test]" value="on" />',
              '</label>',
              '<label class=l2>',
                '<input type="radio" name="baz[test]" value="off" />',
              '</label>',
            '</fieldset>',
            '<fieldset class=f4 data-group=bez>',
              '<div class=validation-wrapper>',
                '<ul><li>Error here</li></ul>',
                // @NOTE: the component assumes fields all live in a "field" wrapper...
                // but that's not always the case in reality. Lots more edge cases
                // to catch here...
                '<div class=field>',
                  '<input class="i4" name="bez[input]" value="bez-f4" />',
                '</div>',
              '</div>',
            '</fieldset>',
            '<button type="submit" />',
          '</form>',
        '</div>'
      ].join("\n");

      this.body = $("body");

      this.body.append(template);

      this.on = sinon.spy($.fn, "on");
    });

    afterEach(function() {
      this.on.restore();
      $("#stub").remove();
    });

    describe("When initialised with valid options", function() {
      beforeEach(function() {
        this.submitSpy = sinon.spy();

        this.f1Spy = sinon.spy();
        this.f2Spy = sinon.spy();

        OLCS.eventEmitter.once("show:foo:*", this.f1Spy);
        OLCS.eventEmitter.once("hide:bar:*", this.f2Spy);

        this.component({
          form: ".stub-form",
          submit: this.submitSpy,
          rulesets: {
            "foo": true,
            "bar": function() {
              return $(".i1").val() !== "";
            },
            "baz": {
              "*": function() {
                return $(".i2").val() === "test";
              },
              "test=off": function() {
                return $(".i1").val() === "off";
              }
            },
            "bez": {
              "*": true,
              "input": function() {
                return $(".i1").val() !== "";
              }
            }
          }
        });

        OLCS.eventEmitter.emit("render");
      });

      it("should bind the correct change listener to the form", function() {
        expect(this.on.getCall(4).args[0]).to.equal("change");
        // NO: function.name not supported in IE8
        //expect(this.on.getCall(3).args[2].name).to.equal("checkForm");
      });

      it("should show the first fieldset", function() {
        expect($(".f1").is(":visible")).to.equal(true);
      });

      it("should not fire the first fieldset's show event", function() {
        expect(this.f1Spy.callCount).to.equal(0);
      });

      it("should hide the second fieldset", function() {
        expect($(".f2").is(":visible")).to.equal(false);
      });

      it("should fire the second fieldset's hide event", function() {
        expect(this.f2Spy.callCount).to.equal(1);
      });

      it("should hide the third fieldset", function() {
        expect($(".f3").is(":visible")).to.equal(false);
      });

      it("should show the fourth fieldset", function() {
        expect($(".f4").is(":visible")).to.equal(true);
      });

      it("should hide the fourth input", function() {
        expect($(".i4").is(":visible")).to.equal(false);
      });

      it("should hide the fourth input's validation wrapper", function() {
        expect($(".validation-wrapper").is(":visible")).to.equal(false);
      });

      describe("When giving the first input a value", function() {
        beforeEach(function() {
          $(".i1").val("fooBar").change();
        });

        it("should show the first fieldset", function() {
          expect($(".f1").is(":visible")).to.equal(true);
        });

        it("should show the second fieldset", function() {
          expect($(".f2").is(":visible")).to.equal(true);
        });

        it("should hide the third fieldset", function() {
          expect($(".f3").is(":visible")).to.equal(false);
        });

        it("should show the fourth input", function() {
          expect($(".i4").is(":visible")).to.equal(true);
        });

        it("should show the fourth input's validation wrapper", function() {
          expect($(".validation-wrapper").is(":visible")).to.equal(true);
        });

        describe("When giving the second input a specific value", function() {
          beforeEach(function() {
            $(".i2").val("test").change();
          });

          it("should show the first fieldset", function() {
            expect($(".f1").is(":visible")).to.equal(true);
          });

          it("should show the second fieldset", function() {
            expect($(".f2").is(":visible")).to.equal(true);
          });

          it("should show the third fieldset", function() {
            expect($(".f3").is(":visible")).to.equal(true);
          });

          it("should show the third fieldset's first label", function() {
            expect($(".l1").is(":visible")).to.equal(true);
          });

          it("should hide the third fieldset's second label", function() {
            expect($(".l2").is(":visible")).to.equal(false);
          });

          describe("When updating the first input's value", function() {
            beforeEach(function() {
              $(".i1").val("off").change();
            });

            it("should show the first fieldset", function() {
              expect($(".f1").is(":visible")).to.equal(true);
            });

            it("should show the second fieldset", function() {
              expect($(".f2").is(":visible")).to.equal(true);
            });

            it("should show the third fieldset", function() {
              expect($(".f3").is(":visible")).to.equal(true);
            });

            it("should show the third fieldset's first label", function() {
              expect($(".l1").is(":visible")).to.equal(true);
            });

            it("should show the third fieldset's second label", function() {
              expect($(".l2").is(":visible")).to.equal(true);
            });
          });
        });
      });

      describe.skip("When submitting the form", function() {
        beforeEach(function() {
          // fails, submits the form
          $(".stub-form").submit();
        });

        it("should invoke the supplied submit handler", function() {
          expect(this.submitSpy.callCount).to.equal(1);
        });
      });
    });

    describe("When initialised with an invalid ruleset selector", function() {
      beforeEach(function() {
        try {
          this.component({
            form: ".stub-form",
            rulesets: {
              "foo": {
                "*": true,
                "invalid:selector": true
              }
            }
          });

          OLCS.eventEmitter.emit("render");
        } catch (e) {
          this.e = e;
        }
      });

      it("should throw the correct error", function() {
        expect(this.e.message).to.equal("Unsupported left-hand selector: invalid");
      });
    });
  });
});

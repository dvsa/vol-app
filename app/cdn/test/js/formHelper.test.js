describe("OLCS.formHelper", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.formHelper;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  it("should also expose various helper functions", function() {
    expect(this.component.fieldset).to.be.a("function");
    expect(this.component.input).to.be.a("function");
    expect(this.component.findInput).to.be.a("function");
    expect(this.component.pressButton).to.be.a("function");
    expect(this.component.buttonPressed).to.be.a("function");
    expect(this.component.isChecked).to.be.a("function");
    expect(this.component.isSelected).to.be.a("function");
    expect(this.component.containsErrors).to.be.a("function");
    expect(this.component.containsWarnings).to.be.a("function");
    expect(this.component.containsElement).to.be.a("function");
    expect(this.component.clearErrors).to.be.a("function");
    expect(this.component.render).to.be.a("function");
    expect(this.component.selectRadio).to.be.a("function");
  });

  describe("Given a stubbed jQuery object", function() {
    beforeEach(function() {
      this.find = sinon.stub($.fn, "find").returns("result");
    });

    afterEach(function() {
      this.find.restore();
    });

    describe("fieldset", function() {
      beforeEach(function() {
        this.result = this.component.fieldset("foo");
      });

      it("invokes the expected jQuery selector", function() {
        expect(this.find.secondCall.args[0]).to.equal("fieldset[data-group='foo']");
      });

      it("returns the result from $.find", function() {
        expect(this.result).to.equal("result");
      });
    });

    describe("input", function() {
      beforeEach(function() {
        this.result = this.component.input("foo", "bar");
      });

      it("invokes the expected jQuery selector", function() {
        expect(this.find.secondCall.args[0]).to.equal("[name=foo\\[bar\\]]");
      });

      it("returns the result from $.find", function() {
        expect(this.result).to.equal("result");
      });
    });

    describe("When invoking the helper function", function() {
      beforeEach(function() {
        this.fieldset = sinon.stub(this.component, "fieldset");
        this.input = sinon.stub(this.component, "input");
      });

      afterEach(function() {
        this.fieldset.restore();
        this.input.restore();
      });

      describe("With a single argument", function() {
        beforeEach(function() {
          this.result = this.component("foo");
        });

        it("invokes the fieldset method", function() {
          expect(this.fieldset.callCount).to.equal(1);
        });
      });

      describe("With two arguments", function() {
        beforeEach(function() {
          this.result = this.component("foo", "bar");
        });

        it("invokes the input method", function() {
          expect(this.input.callCount).to.equal(1);
        });
      });
    });
  });

  describe("Given a stubbed DOM", function() {
    beforeEach(function() {
      var template = [
        '<div id="stub">',
          '<form class="stub-form" action=/foo method=post>',
            '<fieldset class=f1 data-group=baz>',
              '<label class=l1>',
                '<input type="radio" name="baz[test]" value="Y" checked="" />',
              '</label>',
              '<label class=l2>',
                '<input type="radio" name="baz[test]" value="N" />',
              '</label>',
            '</fieldset>',
            '<fieldset class=f1 data-group=foo>',
              '<label class=l1>',
                '<select name="foo[bar]">',
                  '<option value="cake" selected="selected">Cake</option>',
                  '<option value="bar">Bar</option>',
                '</select>',
              '</label>',
            '</fieldset>',
            '<button type=submit name=save>Save</button>',
            '<button type=submit name=cancel>Cancel</button>',
          '</form>',
        '</div>'
      ].join("\n");

      this.body = $("body");

      this.body.append(template);
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("pressButton", function() {
      beforeEach(function() {
        this.component.pressButton(
          $(".stub-form"),
          $(".stub-form button:first")
        );
      });

      it("adds a hidden input with the value of the button", function() {
        expect($(".form__action").attr("name")).to.equal("save");
      });

      describe("buttonPressed", function() {
        describe("When invoked on the button which has been pressed", function() {
          beforeEach(function() {
            this.result = this.component.buttonPressed(
              $(".stub-form"),
              "save"
            );
          });

          it("returns the correct result", function() {
            expect(this.result).to.equal(true);
          });
        });

        describe("When invoked on a button which has not been pressed", function() {
          beforeEach(function() {
            this.result = this.component.buttonPressed(
              $(".stub-form"),
              "cancel"
            );
          });

          it("returns the correct result", function() {
            expect(this.result).to.equal(false);
          });
        });
      });
    });

    describe("isChecked", function() {
      describe("When invoked on a radio button which is checked", function() {
        beforeEach(function() {
          this.result = this.component.isChecked("baz", "test");
        });

        it("returns the correct result", function() {
          expect(this.result).to.equal(true);
        });
      });

      describe("When invoked on a radio button which does not exist", function() {
        beforeEach(function() {
          this.result = this.component.isChecked("baz", "fake");
        });

        it("returns the correct result", function() {
          expect(this.result).to.equal(false);
        });
      });
    });

    describe("isSelected", function() {
      describe("When invoked on a select element checking a selected value", function() {
        beforeEach(function() {
          this.result = this.component.isSelected("foo", "bar", "cake");
        });

        it("returns the correct result", function() {
          expect(this.result).to.equal(true);
        });
      });

      describe("When invoked on a select element checking an unselected value", function() {
        beforeEach(function() {
          this.result = this.component.isSelected("foo", "bar", "baz");
        });

        it("returns the correct result", function() {
          expect(this.result).to.equal(false);
        });
      });
    });

    describe("containsErrors", function() {
      describe("Given a stubbed DOM with errors", function() {
        beforeEach(function() {
          var template = [
            "<div id=stub2>",
              "<div class='validation-summary'>Error summary</div>",
            "</div>"
          ].join("\n");

          $("body").append(template);
        });

        afterEach(function() {
          $("#stub2").remove();
        });

        describe("When invoked", function() {
          beforeEach(function() {
            this.result = this.component.containsErrors($("#stub2"));
          });

          it("returns true", function() {
            expect(this.result).to.equal(true);
          });
        });
      });

      describe("Given a stubbed DOM without errors", function() {
        beforeEach(function() {
          var template = [
            "<div id=stub2>",
              "No errors here",
            "</div>"
          ].join("\n");

          $("body").append(template);
        });

        afterEach(function() {
          $("#stub2").remove();
        });

        describe("When invoked", function() {
          beforeEach(function() {
            this.result = this.component.containsErrors($("#stub2"));
          });

          it("returns false", function() {
            expect(this.result).to.equal(false);
          });
        });
      });
    });

    describe("clearErrors", function() {
      describe("Given a stubbed DOM", function() {
        beforeEach(function() {
          var template = [
            "<div id=stub2>",
              "<div id=summary class='validation-summary'>Error summary</div>",
              "<div id=wrapper1 class='validation-wrapper'>",
                "<ul id=list1><li>Error message</li></ul>",
                "<ul id=list2><li>Not an error message</li></ul>",
              "</div>",
              "<div id=wrapper2 class='validation-wrapper'>",
                "<ul id=list3><li>Error message</li></ul>",
              "</div>",
            "</div>"
          ].join("\n");

          $("body").append(template);
        });

        afterEach(function() {
          $("#stub2").remove();
        });

        describe("When invoked", function() {
          beforeEach(function() {
            this.component.clearErrors();
          });

          it("removes the expected elements", function() {
            expect($("#summary").length).to.equal(0);
            expect($("#list1").length).to.equal(0);
            expect($("#list3").length).to.equal(0);
          });

          it("does not remove the expected list element", function() {
            expect($("#list2").length).to.equal(1);
          });

          it("removes any validation wrapper classes", function() {
            expect($("#wrapper1").hasClass("validation-wrapper")).to.equal(false);
            expect($("#wrapper2").hasClass("validation-wrapper")).to.equal(false);
          });
        });
      });
    });
  });

  describe("selectRadio", function() {
    describe("Given a stubbed DOM", function() {
      beforeEach(function() {
        var template = [
          "<div id=stub3>",
            "<fieldset data-group=bar>",
              "<input id=r1 name=bar[foo] value=Y type=radio />",
              "<input id=r2 name=bar[foo] value=N type=radio />",
              "<input id=r3 name=bar[foo] value=Z type=radio />",
            "</fieldset>",
          "</div>"
        ].join("\n");

        $("body").append(template);
      });

      afterEach(function() {
        $("#stub3").remove();
      });

      describe("When invoked", function() {
        beforeEach(function() {
          this.component.selectRadio("bar", "foo", "Y");
        });

        it("checks the correct element", function() {
          expect($("#r1").is(":checked")).to.equal(true);
        });

        it("does not check the other elements", function() {
          expect($("#r2").is(":checked")).to.equal(false);
          expect($("#r3").is(":checked")).to.equal(false);
        });
      });
    });
  });
});

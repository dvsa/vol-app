describe("OLCS.selectBox", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.selectBox;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed DOM", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<div>",
            "<label id=l1>",
              "<input type='radio' name=radio checked='checked' />",
            "</label>",
            "<label id=l2>",
              "<input type='radio' name=radio id=r2 />",
            "</label>",
          "</div>",
          "<div>",
            "<label id=l3>",
              "<input type=checkbox name=checkbox id=c1 />",
            "</label>",
          "</div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("When invoked", function() {
      beforeEach(function() {
        this.component();
      });

      it("applies the selected class to the checked radio's parent", function() {
        expect($("#l1").hasClass("selected")).to.equal(true);
      });

      it("does not apply the selected class to the non-checked radio's parent", function() {
        expect($("#l2").hasClass("selected")).to.equal(false);
      });

      it("does not apply the selected class to checkbox input's parent", function() {
        expect($("#l3").hasClass("selected")).to.equal(false);
      });

      describe("When selecting the other radio button", function() {
        beforeEach(function() {
          $("#r2").click();
        });

        it("applies the selected class to the checked radio's parent", function() {
          expect($("#l2").hasClass("selected")).to.equal(true);
        });

        it("removes the selected class from the non-checked radio's parent", function() {
          expect($("#l1").hasClass("selected")).to.equal(false);
        });
      });

      describe("When checking the checkbox", function() {
        beforeEach(function() {
          $("#c1").click();
        });

        it("applies the selected class to its parent", function() {
          expect($("#l3").hasClass("selected")).to.equal(true);
        });

        describe("When unchecking the checkbox", function() {
          beforeEach(function() {
            $("#c1").click();
          });

          it("removes the selected class to its parent", function() {
            expect($("#l3").hasClass("selected")).to.equal(false);
          });
        });
      });
    });
  });
});

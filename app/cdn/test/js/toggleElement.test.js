describe("OLCS.toggleElement", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.toggleElement;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed DOM with a trigger and a hidden target element", function() {
    beforeEach(function() {
      $("body").append([
        "<div id=stub>",
          "<div id=trigger>Menu toggle",
            "<div id=target>Menu</div>",
          "</div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("When invoked without any options", function() {
      beforeEach(function() {
        try {
          this.component();
        } catch (e) {
          this.e = e;
        }
      });

      it("throws the expected error", function() {
        expect(this.e.message).to.equal("OLCS.toggleElement requires a triggerSelector and an targetSelector option");
      });
    });

    describe("When invoked without a triggerSelector", function() {
      beforeEach(function() {
        try {
          this.component({
            triggerSelector: '#foo'
          });
        } catch (e) {
          this.e = e;
        }
      });

      it("throws the expected error", function() {
        expect(this.e.message).to.equal("OLCS.toggleElement requires a triggerSelector and an targetSelector option");
      });
    });

    describe("When invoked without a targetSelector", function() {
      beforeEach(function() {
        try {
          this.component({
            targetSelector: '#foo'
          });
        } catch (e) {
          this.e = e;
        }
      });

      it("throws the expected error", function() {
        expect(this.e.message).to.equal("OLCS.toggleElement requires a triggerSelector and an targetSelector option");
      });
    });


    describe("When invoked with valid options", function() {
      beforeEach(function() {
        this.options = {
          triggerSelector: "#trigger",
          targetSelector: "#target"
        };
        this.component(this.options);
      });

      describe("When the trigger element is clicked and the target element is hidden", function() {
        beforeEach(function() {
          $("#target").hide();
          $("#trigger").removeClass('active').click();
        });

        it("the trigger should now have the class 'active'", function() {
          expect($("#trigger").hasClass("active")).to.be(true);
        });

        it("and the target element should be shown", function() {
          expect($("#target").is(":visible")).to.be(true);
        });
      });


      describe("When the target element is showing", function() {
        beforeEach(function() {
          $("#target").css("display","block");
          $("#trigger").addClass('active');
        });

        afterEach(function() {
          $("#target").removeAttr("style");
          $("#trigger").removeClass('active');
        });

        describe("and the document is clicked", function() {
          beforeEach(function() {
            $(document).click();
          });

          it("the target is no longer shown", function() {
            expect($("#target").attr("style")).to.be.undefined;
          });

          it("and the trigger no longer has the class 'active'", function() {
             expect($("#trigger").hasClass("active")).to.be(false);
          });
        });

      });
    });
  });
});

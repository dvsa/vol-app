describe("OLCS.notices", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.notices;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed DOM with more than one notice", function() {
    beforeEach(function() {
      $("body").append([
        "<div class=internal id=stub>",
          "<div class=notice-container>",
            "<div class=notice--success>",
              "<p>Message</p>",
            "</div>",
            "<div class=notice--danger>",
              "<p>Message></p>",
            "</div>",
          "</div>",
        "</div>"
      ].join("\n"));
    });

    afterEach(function() {
      $("#stub").remove();
    });

    describe("Given a fake clock", function() {
      beforeEach(function() {
        this.clock = sinon.useFakeTimers();
      });

      afterEach(function() {
        this.clock.restore();
      });

      describe("When invoked", function() {
        beforeEach(function() {
          this.component();
        });

        describe("And the page has rendered", function() {
          beforeEach(function() {
            OLCS.eventEmitter.emit("render");
          });

          it("It should add a 'Close button' to each notice", function() {
            expect($(".notice--success .notice__close").length).to.equal(1);
            expect($(".notice--danger .notice__close").length).to.equal(1);
          });

          describe("When the first notice's close button is clicked", function() {
            beforeEach(function() {
              $(".notice--success .notice__close").click();
            });

            it("It removes the expected element", function() {
              expect($(".notice--danger").length).to.equal(1);
              expect($(".notice--success").length).to.equal(0);
              expect($(".notice-container").length).to.equal(1);
            });

            describe("When the last remaining element's close button is clicked", function() {
              beforeEach(function() {
                $(".notice--danger .notice__close").click();
              });

              it("It removes the container from them DOM", function() {
                expect($(".notice-container").length).to.equal(0);
              });
            });
          });

          describe("After 10 seconds has passed", function() {
            beforeEach(function(){
              this.clock.tick(10400);
            });

            it("It removes the container from them DOM", function() {
              expect($(".notice-container").length).to.equal(0);
            });
          });

        });

      });
    });
  });

  describe("Given a stubbed DOM with the class of either 'modal' or 'one-fifth--right", function() {
    beforeEach(function() {
      $("body").append([
        "<div class=modal "+"one-fifth--right"+">",
          "<div class=notice-container>",
            "<div class=notice--success>",
              "<p>Message</p>",
            "</div>",
          "</div>",
        "</div>"
      ].join("\n"));

    });

    afterEach(function() {
      $(".modal").remove();
    });

    describe("Given a fake clock", function() {
      beforeEach(function() {
        this.clock = sinon.useFakeTimers();
      });

      afterEach(function() {
        this.clock.restore();
      });

      describe("When invoked and the page has rendered", function() {
        beforeEach(function() {
          this.component();
          OLCS.eventEmitter.emit("render");
        });

        describe("After 14000ms seconds has passed", function() {
          beforeEach(function(){
            this.clock.tick(14000);
          });

          it("The notice container should still be present", function() {
            expect($(".notice-container").length).to.equal(1);
          });
        });
      });

    });
  });
});

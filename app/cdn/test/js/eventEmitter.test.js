describe("OLCS.eventEmitter", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.eventEmitter;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should expose the correct public interface", function() {
    expect(this.component.on).to.be.a("function");
    expect(this.component.once).to.be.a("function");
    expect(this.component.emit).to.be.a("function");
  });

  describe("on", function() {
    beforeEach(function() {
      this.spy = sinon.spy();
      this.component.on("event", this.spy);
    });

    describe("Given an emit matching the namespace", function() {
      describe("with no arguments", function() {
        beforeEach(function() {
          this.component.emit("event");
        });

        it("triggers the handler correctly", function() {
          expect(this.spy.callCount).to.equal(1);
        });

        describe("When triggered again", function() {
          beforeEach(function() {
            this.component.emit("event");
          });

          it("triggers the handler again", function() {
            expect(this.spy.callCount).to.equal(2);
          });
        });
      });

      describe("with arguments", function() {
        beforeEach(function() {
          this.component.emit("event", ["foo", "bar"]);
        });

        it("triggers the handler correctly", function() {
          expect(this.spy.firstCall.args[0]).to.equal("foo");
          expect(this.spy.firstCall.args[1]).to.equal("bar");
        });
      });
    });
  });

  describe("once", function() {
    beforeEach(function() {
      this.spy = sinon.spy();
      this.component.once("event", this.spy);
    });

    describe("Given an emit matching the namespace", function() {
      describe("with no arguments", function() {
        beforeEach(function() {
          this.component.emit("event");
        });

        it("triggers the handler correctly", function() {
          expect(this.spy.callCount).to.equal(1);
        });

        describe("When triggered again", function() {
          beforeEach(function() {
            this.component.emit("event");
          });

          it("does not trigger the handler again", function() {
            expect(this.spy.callCount).to.equal(1);
          });
        });
      });
    });
  });
});

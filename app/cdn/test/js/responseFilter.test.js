describe("OLCS.filterResponse", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.filterResponse;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("When invoked with invalid arguments", function() {
    beforeEach(function() {
      try {
        this.component();
      } catch (e) {
        this.e = e;
      }
    });

    it("throws the expected error", function() {
      expect(this.e.message).to.equal("OLCS.filterResponse requires a container argument");
    });
  });

  describe("When invoked with valid arguments", function() {
    beforeEach(function() {
      this.result = this.component(".filter", ".container");
    });

    it("returns a function", function() {
      expect(this.result).to.be.a("function");
    });

    describe("When invoking the returned function", function() {
      describe("With the filter as the top-level element", function() {
        beforeEach(function() {
          this.formHelper = sinon.spy(OLCS.formHelper, "render");

          this.result({
            body: "<div class=filter>Inner content</div>"
          });
        });

        afterEach(function() {
          this.formHelper.restore();
        });

        it("should render the correct filtered content", function() {
          expect(this.formHelper.firstCall.args[0]).to.equal('.container');
          expect(this.formHelper.firstCall.args[1]).to.equal('Inner content');
        });
      });

      describe("With the filter as a nested element", function() {
        beforeEach(function() {
          this.formHelper = sinon.spy(OLCS.formHelper, "render");

          this.result({
            body: "<div>Top<div>Outer<div class=filter>Inner content</div></div></div>"
          });
        });

        afterEach(function() {
          this.formHelper.restore();
        });

        it("should render the correct filtered content", function() {
          expect(this.formHelper.firstCall.args[0]).to.equal('.container');
          expect(this.formHelper.firstCall.args[1]).to.equal('Inner content');
        });
      });

      describe("With the filter not present at all", function() {
        beforeEach(function() {
          this.formHelper = sinon.spy(OLCS.formHelper, "render");

          this.result({
            body: "<div>Top<div>Outer<div>Inner content</div></div></div>"
          });
        });

        afterEach(function() {
          this.formHelper.restore();
        });

        it("should render the correct filtered content", function() {
          expect(this.formHelper.firstCall.args[0]).to.equal('.container');
          expect(this.formHelper.firstCall.args[1]).to.equal(
            "<div>Top<div>Outer<div>Inner content</div></div></div>"
          );
        });
      });
    });
  });

  describe("When invoked with no filter", function() {
    beforeEach(function() {
      this.result = this.component(null, ".container");
    });

    describe("When invoking the returned function", function() {
      beforeEach(function() {
        this.formHelper = sinon.spy(OLCS.formHelper, "render");

        this.result({
          body: "<div class=filter>Inner content</div>"
        });
      });

      afterEach(function() {
        this.formHelper.restore();
      });

      it("should render the response body", function() {
        expect(this.formHelper.firstCall.args[0]).to.equal('.container');
        expect(this.formHelper.firstCall.args[1]).to.equal("<div class=filter>Inner content</div>");
      });
    });
  });
});

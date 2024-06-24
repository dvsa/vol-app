describe("OLCS.queryString", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.queryString;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should expose the correct public interface", function() {
    expect(this.component.parse).to.be.a("function");
  });

  describe("parse", function() {
    describe("When invoked with a string", function() {
      describe("With no query string delimiter", function() {
        beforeEach(function() {
          this.result = this.component.parse("foo=1&bar=1");
        });

        it("returns an empty object", function() {
          expect(this.result).to.be.an("object");
          expect(this.result).to.be.empty();
        });
      });

      describe("With a query string delimiter", function() {
        describe("With a standard query string", function() {
          beforeEach(function() {
            this.result = this.component.parse("?foo=1&bar=2");
          });

          it("returns the expected object", function() {
            expect(this.result.foo).to.equal('1');
            expect(this.result.bar).to.equal('2');
          });
        });

        describe("With some keys without values", function() {
          beforeEach(function() {
            this.result = this.component.parse("?foo&bar&baz=test");
          });

          it("returns the expected object", function() {
            expect(this.result.foo).to.equal('');
            expect(this.result.bar).to.equal('');
            expect(this.result.baz).to.equal('test');
          });
        });
      });
    });
  });
});

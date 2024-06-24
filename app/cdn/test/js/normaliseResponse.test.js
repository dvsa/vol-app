describe("OLCS.normaliseResponse", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.normaliseResponse;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("When invoked with no arguments", function() {
    beforeEach(function() {
      var self = this;

      try {
        this.result = this.component();
      } catch (e) {
        this.e = e;
      }
    });

    it("throws the expected error", function() {
      expect(this.e.message).to.equal("OLCS.normaliseResponse requires at least a callback argument");
    });
  });

  describe("When invoked with a single function argument", function() {
    beforeEach(function() {
      var self = this;

      this.response = {};

      this.result = this.component(function(response) {
        self.response = response;
      });
    });

    it("returns a function", function() {
      expect(this.result).to.be.a("function");
    });

    describe("When invoking the returned function", function() {
      describe("With an object", function() {
        beforeEach(function() {
          this.result({foo: "bar"});
        });

        it("returns the unmodified response", function() {
          expect(this.response.foo).to.equal("bar");
        });
      });

      describe("With a string", function() {
        describe("With no known DOM elements", function() {
          beforeEach(function() {
            this.result("this is a simple response string");
          });

          it("returns the correct default response code", function() {
            expect(this.response.status).to.equal(200);
          });

          it("returns an empty title", function() {
            expect(this.response.title).to.equal("");
          });

          it("returns the original response as the body property", function() {
            expect(this.response.body).to.equal("this is a simple response string");
          });

          it("returns no errors", function() {
            expect(this.response.hasErrors).to.equal(false);
          });

          it("returns no warnings", function() {
            expect(this.response.hasWarnings).to.equal(false);
          });
        });

        describe("With known DOM elements", function() {
          describe("With a simple body", function() {
            beforeEach(function() {
              var result = [
                "<div>",
                  "<div class=js-title>title</div>",
                  "<div class=js-body>body</div>",
                  "<div class=js-script>script</div>",
                "</div>"
              ].join("\n");
              this.result(result);
            });

            it("returns the correct default response code", function() {
              expect(this.response.status).to.equal(200);
            });

            it("returns the corret title", function() {
              expect(this.response.title).to.equal("title");
            });

            it("returns the correct body", function() {
              expect(this.response.body).to.equal("bodyscript");
            });

            it("returns no errors", function() {
              expect(this.response.hasErrors).to.equal(false);
            });

            it("returns no warnings", function() {
              expect(this.response.hasWarnings).to.equal(false);
            });
          });

          describe("With an inner body", function() {
            beforeEach(function() {
              var result = [
                "<div>",
                  "<div class=js-title>title</div>",
                  "<div class=js-body>",
                    "body",
                    "<div class=js-body__main>inner</div>",
                  "</div>",
                  "<div class=js-script>script</div>",
                "</div>"
              ].join("\n");
              this.result(result);
            });

            it("returns the inner body", function() {
              expect(this.response.body).to.equal("innerscript");
            });
          });
        });
      });
    });
  });
});

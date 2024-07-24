describe("OLCS.formHandler", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.formHandler;
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
          '<form action="/foo" method="get" class="js-form">',
            '<input name="bar" type="text" />',
            '<input type="submit" />',
          '</form>',
          '<div class="container"></div>',
        '</div>'
      ].join("\n");

      this.body = $("body");

      this.body.append(template);

      this.on = sinon.spy($.prototype, "on");
    });

    afterEach(function() {
      this.on.restore();
      $("#stub").remove();
    });

    describe("when initialised with valid options", function() {
      describe("with no filter option", function() {
        beforeEach(function() {
          this.options = {
            form: ".js-form",
            hideSubmit: true,
            container: ".container"
          };
          this.component(this.options);
        });

        afterEach(function() {
          $(document).off("submit");
          $(document).off("change");
        });

        it("hides the submit button", function() {
          expect($("[type=submit]").is(":hidden")).to.equal(true);
        });

        describe("Given a stubbed OLCS.submitForm component", function() {
          beforeEach(function() {
            this.ajax = sinon.stub(OLCS, "submitForm");
          });

          afterEach(function() {
            this.ajax.restore();
          });

          describe("When submitting the target form", function() {
            beforeEach(function() {
              this.body.find("form").submit();
            });

            it("invokes an AJAX request", function() {
              expect(this.ajax.callCount).to.equal(1);
            });

            describe("Given the request returns successfully", function() {
              beforeEach(function() {
                this.ajax.yieldTo("success", "<div class=response>I am a response</div>");
              });

              it("inserts the response into the correct container", function() {
                expect($("#stub .container .response").html()).to.equal("I am a response");
              });
            });
          });

          describe("When triggering the form's onchange event", function() {
            beforeEach(function() {
              this.body.find("form").change();
            });

            it("invokes an AJAX request", function() {
              expect(this.ajax.callCount).to.equal(1);
            });
          });
        });
      });

      describe("with a filter option", function() {
        beforeEach(function() {
          this.options = {
            form: ".js-form",
            hideSubmit: true,
            container: ".container",
            filter: ".response"
          };
          this.component(this.options);
        });

        afterEach(function() {
          $(document).off("submit");
          $(document).off("change");
        });

        describe("Given a stubbed OLCS.submitForm component", function() {
          beforeEach(function() {
            var self = this;

            this.ajax = sinon.stub(OLCS, "submitForm");
          });

          afterEach(function() {
            this.ajax.restore();
          });

          describe("When submitting the target form", function() {
            beforeEach(function() {
              this.body.find("form").submit();
            });

            describe("Given the request returns successfully", function() {
              beforeEach(function() {
                this.ajax.yieldTo("success", "<div class=outer><div class=response>I am a response</div></div>");
              });

              it("inserts the response into the correct container", function() {
                expect($("#stub .container").html()).to.equal("I am a response");
              });
            });
          });
        });
      });
    });

    describe("unbind", function() {
      describe("Given an initialised component", function() {
        beforeEach(function() {
          this.off = sinon.spy($.prototype, "off");
          this.handler = this.component({
            form: ".js-form",
            hideSubmit: true,
            container: ".container"
          });
        });

        describe("When the handler is unbound", function() {
          beforeEach(function() {
            this.handler.unbind();
          });

          it("unbinds all listeners", function() {
            expect(this.off.callCount).to.eql(this.on.callCount);
          });
        });
      });
    });
  });
});

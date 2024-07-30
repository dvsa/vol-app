/**
 * OLCS.cascadeInput
 *
 * grunt test:single --target=cascadeInput
 */


 describe("OLCS.cascadeInput", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.cascadeInput;
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
      '<input name="bar" class="source" type="text" />',
      '<select name="baz" class="dest"></select>',
      '<input type="submit" id="submitId"/>',
      '</form>',
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

    describe("When initialised with no process callback", function() {
      beforeEach(function() {
        try {
          this.component({
            source: ".source",
            dest: ".dest"
          });
        } catch (e) {
          this.error = e;
        }
      });

      it("throws the correct error", function() {
        expect(this.error.message).to.equal("Please provide a 'process' function or 'url' string");
      });
    });

    describe("When initialised with valid options", function() {
      beforeEach(function() {
        this.spy = sinon.spy();
        this.component({
          source: ".source",
          dest: ".dest",
          trap: true,
          process: this.spy
        });
      });

      afterEach(function() {
        $(document).off("change");
      });

      describe("When the source value changes", function() {
        beforeEach(function() {
          $(".source").val("foo").change();
        });

        it("invokes the process method", function() {
          expect(this.spy.callCount).to.equal(1);
        });

        describe("When the process method returns", function() {
          beforeEach(function() {
            var data = [
            {value: "1", label: "One"},
            {value: "2", label: "Two"}
            ];
            this.spy.yield(data);
          });

          it("updates the destination options", function() {
            var options = $(".dest option");
            expect(options.length).to.equal(2);

            expect(options.eq(0).val()).to.equal("1");
            expect(options.eq(1).val()).to.equal("2");

            expect(options.eq(0).html()).to.equal("One");
            expect(options.eq(1).html()).to.equal("Two");
          });
        });
      });
    });

    describe("When initialised with a URL option", function() {
      beforeEach(function() {
        this.spy = sinon.spy();
        this.component({
          source: ".source",
          dest: ".dest",
          trap: true,
          url: "/foo",
          disableSubmit: "submitId"
        });
      });

      afterEach(function() {
        $(document).off("change");
      });

      describe("Given a stubbed ajax mechanism", function() {
        beforeEach(function() {
          this.get = sinon.stub(OLCS, "ajax");
        });

        afterEach(function() {
          this.get.restore();
        });

        describe("When the source value changes", function() {
          beforeEach(function() {
            $(".source").val("test123").change();
          });

          it("invokes jQuery.get", function() {
            expect(this.get.callCount).to.equal(1);
          });

          it("with the correct arguments", function() {
            expect(this.get.firstCall.args[0].url).to.equal("/foo/test123");
            expect(this.get.firstCall.args[0].success).to.be.a("function");
          });
        });
      });

      describe("given a stubbed XHR mechanism", function(){
        beforeEach(function(){
          this.xhr = sinon.useFakeXMLHttpRequest();
          this.requests = [];
          this.xhr.onCreate = function(xhr) {
              this.requests.push(xhr);
          }.bind(this);
        });

        afterEach(function() {
          this.xhr.restore();
        });

        describe("When the source value changes", function() {
          beforeEach(function() {
            $(".source").val("test123").change();
            this.submitButton = document.getElementById("submitId");
          });

          it("should disable the submit button", function(){
            expect(this.submitButton.disabled).to.be(true);
          });

          describe("when the ajax call completes", function(){
            beforeEach(function(){
              this.requests[0].respond(200, { 'Content-Type': 'text/json' }, '{"foo":"bar"}');
            });

            it("should enable the submit button", function(){
              expect(this.submitButton.disabled).to.be(false);
            });
          })
        });
      });
    });
  });
});



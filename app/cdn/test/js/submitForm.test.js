describe("OLCS.submitForm", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.submitForm;
  });

  it("should be defined", function() {
    expect(this.component).to.exist;
  });

  it("should be a function", function() {
    expect(this.component).to.be.a("function");
  });

  describe("Given a stubbed OLCS.ajax method", function() {
    beforeEach(function() {
      this.ajax = sinon.stub(OLCS, "ajax");
    });

    afterEach(function() {
      this.ajax.restore();
    });

    describe("When invoked with a form and success callback", function() {
      beforeEach(function() {
        var attr = sinon.stub();
        attr.onCall(0).returns("/foo");
        attr.onCall(1).returns("GET");

        var serialize = sinon.stub();
        serialize.returns("bar=1&baz=2");

        var form = {
          attr: attr,
          serialize: serialize,
          find: function() {
            return {
              not: function() {
                return {
                  attr: sinon.spy(),
                  removeAttr: sinon.spy()
                }
              }
            };
          }
        };
        this.success = function() {};

        this.component({
          form: form,
          success: this.success
        });
      });

      it("invokes OLCS.ajax with the expected parameters", function() {
        var args = this.ajax.firstCall.args[0];

        expect(args.url).to.equal("/foo");
        expect(args.method).to.equal("GET");
        expect(args.data).to.equal("bar=1&baz=2");
        expect(args.success).to.equal(this.success);
        expect(args.error).to.be.a("function");
      });
    });

    describe("When invoked with an extra error callback", function() {
      beforeEach(function() {
        var attr = sinon.stub();
        attr.onCall(0).returns("/foo");
        attr.onCall(1).returns("GET");

        var serialize = sinon.stub();
        serialize.returns("bar=1&baz=2");

        var form = {
          attr: attr,
          serialize: serialize,
          find: function() {
            return {
              not: function() {
                return {
                  attr: sinon.spy(),
                  removeAttr: sinon.spy()
                }
              }
            };
          }
        };
        this.success = function() {};
        this.error = function() {};

        this.component({
          form: form,
          success: this.success,
          error: this.error
        });
      });

      it("invokes OLCS.ajax with the expected parameters", function() {
        var args = this.ajax.firstCall.args[0];

        expect(args.url).to.equal("/foo");
        expect(args.method).to.equal("GET");
        expect(args.data).to.equal("bar=1&baz=2");
        expect(args.success).to.equal(this.success);
        expect(args.error).to.equal(this.error);
      });
    });
  });
});

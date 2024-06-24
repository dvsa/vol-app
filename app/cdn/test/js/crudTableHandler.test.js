/**
 * OLCS.modalLink
 *
 * grunt test:single --target=crudTableHandler
 */


/**
 * This prevents a page reload, which causes errors when runnin the test. 
 * These errors are difficult to trace in Karman, see following issue:
 * https://github.com/karma-runner/karma/issues/1101
 * 
 */
beforeEach(function(){
  window.onbeforeunload = function(){
    return 'Oh no!'; 
  }
});

describe("OLCS.crudTableHandler", function() {
  "use strict";

  beforeEach(function() {
    this.component = OLCS.crudTableHandler;
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
        '<form id="stub" method="post" action="/baz">',
          '<div class=table__header>',
            '<input name=action value=Action1  id=submit1 />',
            '<input name=action value=Action2  id=submit2 />',
          '</div>',
          '<div class=table__wrapper>',
            '<div class="results-settings">',
              '<a href=/foo>bar</a>',
            '</div>',
          '</div>',
        '</form>'
      ].join("\n");

      this.body = $("body");

      this.body.append(template);

      

      this.on = sinon.spy($.fn, "on");
    });

    afterEach(function() {
      this.on.restore();
      $("#stub").remove();
    });

    describe("when initialised", function() {
      beforeEach(function() {
        this.conditionalButton = sinon
          .stub(OLCS, "conditionalButton")
          .returns({
            check: sinon.spy()
          });
        this.component({});
      });

      afterEach(function() {
        this.conditionalButton.restore();
        $(document).off("click");
      });

      it("binds a click handler to the correct selectors", function() {
        var str = ".table__header button:not(.js-disable-crud),.table__wrapper button[type=submit],.table__empty button";
        expect(this.on.firstCall.args[0]).to.equal("click");
        expect(this.on.firstCall.args[1]).to.equal(str);
      });

      describe("When triggering the on click handler", function() {
        beforeEach(function() {

            this.submitFormStub = sinon.stub(OLCS, "submitForm");
            this.pressButtonStub = sinon.stub(OLCS.formHelper, "pressButton");

            this.component({
               selector: "#submit1"
             });
            $('#submit1').click();
        });

        afterEach(function() {
          this.submitFormStub.restore();
          this.pressButtonStub.restore();
          
        });

        // @TODO fix and re-implement
        it("should call the submit form function", function() {
          expect(this.submitFormStub.callCount).to.eql(1);
        });

        it("should mark the button as pressed", function(){
          expect(this.pressButtonStub.callCount).to.eql(1);        
        })

      });

      describe("given a stubbed ajax mechanism",function(){
        beforeEach(function(){
          this.xhr = sinon.useFakeXMLHttpRequest();
            this.requests = [];
            this.xhr.onCreate = function(xhr) {
                this.requests.push(xhr);
            }.bind(this);
          });
        afterEach(function(){
          this.xhr.restore();
        });

        describe("when clicking the button", function(){
          beforeEach(function() {
            this.component({
               selector: "#submit1"
             });
            $('#submit1').click();
          });

          afterEach(function(){
            this.requests = [];
          });

          it("should make an AJAX call", function(){
            expect(this.requests.length).to.be(1);
            expect(this.requests[0].url).to.equal('/baz');
          });

          describe("when the ajax call completes with a 200 ok", function(){
            beforeEach(function(){
              var htmlResponse = 
                '<div class="response" id="response">' +
                '<form action="/bar" id="ajaxForm"><button type="submit" id="form-actions[submit]"></form>' +
                '</div>';
              //this.requests[0].respond(200, { 'Content-Type': 'text/html' }, htmlResponse);
            });

            afterEach(function(){
              this.requests = [];
              $("#response").remove();
            });

            it("should add the response to a modal", function(){
              expect($('.modal__wrapper').length).to.equal(0);
              expect($('.response').length).to.equal(0);
            });

            describe("when the modal submit button is pressed", function(){
              beforeEach(function(){
                this.requests = [];
                document.getElementById("form-actions[submit]").click();
                this.ajaxFormAction = $("#ajaxForm").attr('action');
              });

              afterEach(function(){
                this.requests = [];
                delete this.ajaxFormAction;
              });


            });
          });
        });
      });
    });
  });
});

/**
 * OLCS.modalLink
 *
 * grunt test:single --target=modalLink
 */

describe('OLCS.modalLink', function() {

  'use strict';

  beforeEach(function() {
    this.component = OLCS.modalLink;
  });

  it('should be defined', function() {
    expect(this.component).to.exist;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('Given a stubbed DOM', function() {
    beforeEach(function() {
      var template = [
        '<div id="stub">',
          '<a href="/foo" class="js-modal one">foo</a>',
          '<a href="/foo" class="js-modal two"></a>',
          '<a href="/bar" class="js-modal three"></a>',
        '</div>'
      ].join('\n');
      this.body = $('body');
      this.body.append(template);
      this.modal = sinon.stub(OLCS.modal, 'hide');
      this.on = sinon.spy($.prototype, 'on');
    });

    afterEach(function() {
      this.on.restore();
      this.modal.restore();
      $('#stub').remove();
    });

    describe('when initialised with valid options', function() {
      beforeEach(function() {
        this.options = {
          trigger: '.js-modal'
        };
        this.component(this.options);
      });

      afterEach(function() {
        // have to clean up our event handlers otherwise they'll stack up
        $(document).off('click');
      });

      it('binds the correct click listener', function() {
        var call = this.on.getCall(0);
        expect(call.args[0]).to.equal('click');
        expect(call.args[1]).to.equal('.js-modal');
      });

      describe('Given a stubbed ajax mechanism', function() {

        beforeEach(function() {
            this.xhr = sinon.useFakeXMLHttpRequest();
            this.requests = [];
            this.xhr.onCreate = function(xhr) {
                this.requests.push(xhr);
            }.bind(this);
        });

        afterEach(function() {
          this.xhr.restore();
        });

        describe('when triggering a modal', function() {

          beforeEach(function() {
            $('.js-modal:eq(0)').click();
          });

          it('makes an ajax request', function() {
            expect(this.requests.length).to.equal(1);
          });

          it('with the correct URL', function() {
            expect(this.requests[0].url).to.equal('/foo');
          });

          describe('given a valid html respose', function(){

            beforeEach(function(){
              var htmlResponse = '<div class="response">I am a response</div>';
              this.requests[0].respond(200, { 'Content-Type': 'text/html' }, htmlResponse);


              //we also need to fake the authentication response or the modal will not show. 
              var authResponse = {"valid":true,"uid":"usr291","realm":"\/internal","status":200};
              var authResonseJson = JSON.stringify(authResponse);
              this.requests[1].respond(200, { 'Content-Type': 'text/json' }, authResonseJson);

            });

            it('should create a modal', function(){
              expect($('.modal').length).to.equal(1);
            });

            it('should insert the response into the modal', function(){
              expect($('.response').length).to.equal(1);
            });
          
          }); //given a valid html respose      

        }); // when triggering a modal

      }); // Given a stubbed ajax mechanism

    }); // when initialised with valid options

  }); // Given a stubbed DOM

});
